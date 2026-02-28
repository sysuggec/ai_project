<?php

namespace App\Service;

use App\Model\RiskUserModel;
use App\Model\RiskIdentifierModel;
use App\Model\RiskUserAppModel;
use App\Model\RefundOrderModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * 风控服务 - 核心业务逻辑
 */
class RiskService
{
    /**
     * 退款上报
     * 
     * @param array $params 上报参数
     * @return array ['risk_user_id' => int]
     */
    public function refundReport(array $params): array
    {
        $app = $params['app'];
        $orderNo = $params['order_no'];

        // 1. 幂等检查
        $existingOrder = RefundOrderModel::findByAppOrderNo($app, $orderNo);
        if ($existingOrder) {
            return ['risk_user_id' => $existingOrder->risk_user_id];
        }

        // 2. 提取非空账号标识
        $identifiers = $this->extractIdentifiers($params);

        // 3. 查找关联的风险用户
        $riskUserIds = $this->findRiskUserIds($identifiers, $app);

        // 4. 确定最终的风险用户
        $riskUserId = $this->determineRiskUserId($riskUserIds);

        // 5. 使用事务处理
        return Capsule::transaction(function () use ($riskUserId, $riskUserIds, $params, $identifiers, $app, $orderNo) {
            // 5.1 创建或获取风险用户
            if ($riskUserId === null) {
                $riskUser = RiskUserModel::createRiskUser();
                $riskUserId = $riskUser->id;
            }

            // 5.2 处理用户合并
            $uniqueRiskUserIds = array_unique($riskUserIds);
            if (count($uniqueRiskUserIds) > 1) {
                $riskUserId = $this->mergeRiskUsers($uniqueRiskUserIds);
            }

            // 5.3 保存账号标识
            foreach ($identifiers as $type => $value) {
                RiskIdentifierModel::createOrUpdate($riskUserId, $app, $type, $value);
            }

            // 5.4 保存App用户信息
            $this->saveUserAppInfo($riskUserId, $params);

            // 5.5 创建退款订单
            RefundOrderModel::createOrder(
                $riskUserId,
                $app,
                $orderNo,
                (float) $params['refund_amount'],
                $params['payment_channel'] ?? '',
                (int) $params['refund_time']
            );

            return ['risk_user_id' => $riskUserId];
        });
    }

    /**
     * 风控查询
     * 
     * @param array $params 查询参数
     * @return array 风控结果
     */
    public function riskQuery(array $params): array
    {
        // 1. 提取非空账号标识
        $identifiers = $this->extractIdentifiers($params);

        if (empty($identifiers)) {
            return [
                'is_risk' => false,
                'risk_user_id' => null,
                'total_refund_count' => 0,
                'total_refund_amount' => '0.00',
                'refund_summary' => [],
            ];
        }

        // 2. 查找关联的风险用户
        $riskUserId = $this->findFirstRiskUserId($identifiers);

        if ($riskUserId === null) {
            return [
                'is_risk' => false,
                'risk_user_id' => null,
                'total_refund_count' => 0,
                'total_refund_amount' => '0.00',
                'refund_summary' => [],
            ];
        }

        // 3. 查询有效退款订单
        $refundOrders = RefundOrderModel::getValidOrdersByRiskUserId($riskUserId);

        if ($refundOrders->isEmpty()) {
            return [
                'is_risk' => false,
                'risk_user_id' => $riskUserId,
                'total_refund_count' => 0,
                'total_refund_amount' => '0.00',
                'refund_summary' => [],
            ];
        }

        // 4. 汇总退款信息
        $summary = $this->summarizeRefunds($refundOrders);

        return [
            'is_risk' => true,
            'risk_user_id' => $riskUserId,
            'total_refund_count' => $summary['total_count'],
            'total_refund_amount' => $summary['total_amount'],
            'refund_summary' => $summary['by_app'],
        ];
    }

    /**
     * 撤销退款
     * 
     * @param string $app 应用标识
     * @param string $orderNo 订单号
     * @return array ['remaining_refund_count' => int]
     * @throws \Exception
     */
    public function refundCancel(string $app, string $orderNo): array
    {
        return Capsule::transaction(function () use ($app, $orderNo) {
            // 1. 查找订单
            $order = RefundOrderModel::findByAppOrderNo($app, $orderNo);

            if (!$order) {
                throw new \Exception('订单不存在', 2001);
            }

            // 2. 检查状态
            if ($order->isCanceled()) {
                throw new \Exception('订单已撤销', 2002);
            }

            // 3. 撤销订单
            $order->cancel();

            // 4. 返回剩余有效退款数
            $remainingCount = RefundOrderModel::getValidCountByRiskUserId($order->risk_user_id);

            return ['remaining_refund_count' => $remainingCount];
        });
    }

    /**
     * 提取非空账号标识
     */
    private function extractIdentifiers(array $params): array
    {
        $identifiers = [];
        
        $mapping = [
            'phone' => RiskIdentifierModel::TYPE_PHONE,
            'payment_account' => RiskIdentifierModel::TYPE_PAYMENT_ACCOUNT,
            'google_id' => RiskIdentifierModel::TYPE_GOOGLE_ID,
            'facebook_business_id' => RiskIdentifierModel::TYPE_FACEBOOK_BUSINESS_ID,
        ];

        foreach ($mapping as $paramKey => $type) {
            if (!empty($params[$paramKey])) {
                $identifiers[$type] = trim($params[$paramKey]);
            }
        }

        return $identifiers;
    }

    /**
     * 查找所有关联的风险用户ID
     */
    private function findRiskUserIds(array $identifiers, string $app): array
    {
        $riskUserIds = [];

        foreach ($identifiers as $type => $value) {
            // 先在当前App查找
            $riskUserId = RiskIdentifierModel::findRiskUserIdByTypeValue($type, $value, $app);
            
            // 如果当前App没有，在其他App查找
            if ($riskUserId === null) {
                $riskUserId = RiskIdentifierModel::findRiskUserIdByTypeValueAnyApp($type, $value);
            }

            if ($riskUserId !== null) {
                $riskUserIds[] = $riskUserId;
            }
        }

        return $riskUserIds;
    }

    /**
     * 查找第一个关联的风险用户ID
     */
    private function findFirstRiskUserId(array $identifiers): ?int
    {
        foreach ($identifiers as $type => $value) {
            $riskUserId = RiskIdentifierModel::findRiskUserIdByTypeValueAnyApp($type, $value);
            if ($riskUserId !== null) {
                return $riskUserId;
            }
        }
        return null;
    }

    /**
     * 确定最终的风险用户ID
     * 如果找到多个，返回最小的（用于后续合并）
     */
    private function determineRiskUserId(array $riskUserIds): ?int
    {
        if (empty($riskUserIds)) {
            return null;
        }

        return min($riskUserIds);
    }

    /**
     * 合并风险用户
     * 
     * @param array $riskUserIds 要合并的用户ID列表
     * @return int 主用户ID
     */
    private function mergeRiskUsers(array $riskUserIds): int
    {
        // 选择ID最小的作为主用户
        $mainUserId = min($riskUserIds);
        
        // 要合并的其他用户
        $otherUserIds = array_diff($riskUserIds, [$mainUserId]);

        if (empty($otherUserIds)) {
            return $mainUserId;
        }

        $now = time();

        // 迁移账号标识
        RiskIdentifierModel::whereIn('risk_user_id', $otherUserIds)
            ->update(['risk_user_id' => $mainUserId]);

        // 迁移App用户信息
        RiskUserAppModel::whereIn('risk_user_id', $otherUserIds)
            ->update(['risk_user_id' => $mainUserId, 'updated_at' => $now]);

        // 迁移退款订单
        RefundOrderModel::whereIn('risk_user_id', $otherUserIds)
            ->update(['risk_user_id' => $mainUserId, 'updated_at' => $now]);

        // 删除被合并的用户
        RiskUserModel::whereIn('id', $otherUserIds)->delete();

        return $mainUserId;
    }

    /**
     * 保存App用户信息
     */
    private function saveUserAppInfo(int $riskUserId, array $params): void
    {
        $data = [
            'uid' => $params['app_uid'] ?? '',
            'nickname' => $params['nickname'] ?? '',
            'register_time' => $params['register_time'] ?? 0,
            'register_ip' => $params['register_ip'] ?? '',
            'google_nickname' => $params['google_nickname'] ?? '',
            'facebook_nickname' => $params['facebook_nickname'] ?? '',
        ];

        RiskUserAppModel::createOrUpdate($riskUserId, $params['app'], $data);
    }

    /**
     * 汇总退款信息
     */
    private function summarizeRefunds($refundOrders): array
    {
        $totalCount = $refundOrders->count();
        $totalAmount = $refundOrders->sum('refund_amount');

        // 按App分组
        $byApp = [];
        foreach ($refundOrders as $order) {
            $app = $order->app;
            if (!isset($byApp[$app])) {
                $byApp[$app] = [
                    'app' => $app,
                    'refund_count' => 0,
                    'refund_amount' => 0,
                    'app_uid' => '',
                    'nickname' => '',
                ];
            }
            $byApp[$app]['refund_count']++;
            $byApp[$app]['refund_amount'] += (float) $order->refund_amount;
        }

        // 获取每个App的用户信息
        $riskUserId = $refundOrders->first()->risk_user_id;
        $userApps = RiskUserAppModel::where('risk_user_id', $riskUserId)
            ->get()
            ->keyBy('app');

        foreach ($byApp as $app => &$info) {
            if (isset($userApps[$app])) {
                $info['app_uid'] = $userApps[$app]->uid;
                $info['nickname'] = $userApps[$app]->nickname;
            }
            // 格式化金额
            $info['refund_amount'] = number_format($info['refund_amount'], 2, '.', '');
        }

        // 按退款金额降序
        usort($byApp, function ($a, $b) {
            return (float) $b['refund_amount'] <=> (float) $a['refund_amount'];
        });

        return [
            'total_count' => $totalCount,
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'by_app' => array_values($byApp),
        ];
    }
}
