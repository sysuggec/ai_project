<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 风险用户模型
 * 
 * @property int $id
 * @property int $created_at 创建时间（秒级时间戳）
 * @property int $updated_at 更新时间（秒级时间戳）
 */
class RiskUserModel extends Model
{
    protected $table = 't_risk_user';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    /**
     * 关联账号标识
     */
    public function identifiers()
    {
        return $this->hasMany(RiskIdentifierModel::class, 'risk_user_id', 'id');
    }

    /**
     * 关联App用户信息
     */
    public function apps()
    {
        return $this->hasMany(RiskUserAppModel::class, 'risk_user_id', 'id');
    }

    /**
     * 关联退款订单
     */
    public function refundOrders()
    {
        return $this->hasMany(RefundOrderModel::class, 'risk_user_id', 'id');
    }

    /**
     * 获取有效退款订单
     */
    public function validRefundOrders()
    {
        return $this->refundOrders()->where('status', RefundOrderModel::STATUS_VALID);
    }

    /**
     * 创建风险用户
     */
    public static function createRiskUser(): self
    {
        $now = time();
        return self::create([
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
