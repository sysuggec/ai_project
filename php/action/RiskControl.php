<?php

namespace App\Controller;

use App\Service\RiskService;

/**
 * 风控控制器
 */
class RiskControl
{
    /**
     * @var RiskService
     */
    private $riskService;

    public function __construct()
    {
        $this->riskService = new RiskService();
    }

    /**
     * 退款上报接口
     */
    public function refundReport(): array
    {
        $params = $this->getParams();

        // 参数校验
        $this->validateRefundReport($params);

        try {
            $result = $this->riskService->refundReport($params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getCode() ?: 9999, $e->getMessage());
        }
    }

    /**
     * 风控查询接口
     */
    public function riskQuery(): array
    {
        $params = $this->getParams();

        // 参数校验
        $this->validateRiskQuery($params);

        try {
            $result = $this->riskService->riskQuery($params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getCode() ?: 9999, $e->getMessage());
        }
    }

    /**
     * 撤销退款接口
     */
    public function refundCancel(): array
    {
        $params = $this->getParams();

        // 参数校验
        $this->validateRefundCancel($params);

        try {
            $result = $this->riskService->refundCancel($params['app'], $params['order_no']);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getCode() ?: 9999, $e->getMessage());
        }
    }

    /**
     * 获取请求参数
     */
    private function getParams(): array
    {
        $params = [];
        
        // 支持GET和POST
        $request = array_merge($_GET, $_POST);
        
        foreach ($request as $key => $value) {
            if ($key !== 'action') {
                $params[$key] = is_string($value) ? trim($value) : $value;
            }
        }

        return $params;
    }

    /**
     * 校验退款上报参数
     */
    private function validateRefundReport(array $params): void
    {
        $required = ['app', 'order_no', 'refund_amount', 'refund_time', 'app_uid'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->throwError(1001, '参数缺失：' . implode(', ', $missing));
        }

        // 校验金额格式
        if (!is_numeric($params['refund_amount']) || $params['refund_amount'] < 0) {
            $this->throwError(1002, '参数格式错误：refund_amount 必须为非负数');
        }

        // 校验时间戳格式
        if (!is_numeric($params['refund_time']) || $params['refund_time'] < 0) {
            $this->throwError(1002, '参数格式错误：refund_time 必须为有效时间戳');
        }
    }

    /**
     * 校验风控查询参数
     */
    private function validateRiskQuery(array $params): void
    {
        $identifierFields = ['phone', 'payment_account', 'google_id', 'facebook_business_id'];
        $hasIdentifier = false;

        foreach ($identifierFields as $field) {
            if (!empty($params[$field])) {
                $hasIdentifier = true;
                break;
            }
        }

        if (!$hasIdentifier) {
            $this->throwError(1001, '至少需要提供一个账号标识');
        }
    }

    /**
     * 校验撤销退款参数
     */
    private function validateRefundCancel(array $params): void
    {
        $required = ['app', 'order_no'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->throwError(1001, '参数缺失：' . implode(', ', $missing));
        }
    }

    /**
     * 成功响应
     */
    private function success(array $data = []): array
    {
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $data,
        ];
    }

    /**
     * 错误响应
     */
    private function error(int $code, string $msg): array
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => null,
        ];
    }

    /**
     * 抛出错误
     */
    private function throwError(int $code, string $msg): void
    {
        throw new \Exception($msg, $code);
    }
}
