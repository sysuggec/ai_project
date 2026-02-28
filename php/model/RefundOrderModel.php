<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 退款订单模型
 * 
 * @property int $id
 * @property int $risk_user_id 风险用户ID
 * @property string $app 应用标识
 * @property string $order_no 订单号
 * @property string $refund_amount 退款金额
 * @property string $payment_channel 支付渠道
 * @property int $status 状态：1-有效 2-已撤销
 * @property int $refunded_at 退款时间（秒级时间戳）
 * @property int $canceled_at 撤销时间（秒级时间戳）
 * @property int $created_at 创建时间（秒级时间戳）
 * @property int $updated_at 更新时间（秒级时间戳）
 */
class RefundOrderModel extends Model
{
    protected $table = 't_refund_order';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // 订单状态常量
    const STATUS_VALID = 1;      // 有效
    const STATUS_CANCELED = 2;   // 已撤销

    protected $fillable = [
        'risk_user_id',
        'app',
        'order_no',
        'refund_amount',
        'payment_channel',
        'status',
        'refunded_at',
        'canceled_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'risk_user_id' => 'integer',
        'refund_amount' => 'decimal:2',
        'status' => 'integer',
        'refunded_at' => 'integer',
        'canceled_at' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    /**
     * 关联风险用户
     */
    public function riskUser()
    {
        return $this->belongsTo(RiskUserModel::class, 'risk_user_id', 'id');
    }

    /**
     * 根据App和订单号查找
     */
    public static function findByAppOrderNo(string $app, string $orderNo): ?self
    {
        return self::where('app', $app)
            ->where('order_no', $orderNo)
            ->first();
    }

    /**
     * 创建退款订单
     */
    public static function createOrder(int $riskUserId, string $app, string $orderNo, 
        float $refundAmount, string $paymentChannel, int $refundedAt): self
    {
        $now = time();
        return self::create([
            'risk_user_id' => $riskUserId,
            'app' => $app,
            'order_no' => $orderNo,
            'refund_amount' => $refundAmount,
            'payment_channel' => $paymentChannel,
            'status' => self::STATUS_VALID,
            'refunded_at' => $refundedAt,
            'canceled_at' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * 撤销订单
     */
    public function cancel(): bool
    {
        if ($this->status === self::STATUS_CANCELED) {
            return false;
        }

        $this->status = self::STATUS_CANCELED;
        $this->canceled_at = time();
        $this->updated_at = time();
        return $this->save();
    }

    /**
     * 检查是否已撤销
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * 检查是否有效
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    /**
     * 获取风险用户的有效退款订单数
     */
    public static function getValidCountByRiskUserId(int $riskUserId): int
    {
        return self::where('risk_user_id', $riskUserId)
            ->where('status', self::STATUS_VALID)
            ->count();
    }

    /**
     * 获取风险用户的所有有效退款订单
     */
    public static function getValidOrdersByRiskUserId(int $riskUserId)
    {
        return self::where('risk_user_id', $riskUserId)
            ->where('status', self::STATUS_VALID)
            ->orderBy('refunded_at', 'desc')
            ->get();
    }

    /**
     * 更新关联的风险用户ID
     */
    public function updateRiskUserId(int $newRiskUserId): bool
    {
        $this->risk_user_id = $newRiskUserId;
        $this->updated_at = time();
        return $this->save();
    }
}
