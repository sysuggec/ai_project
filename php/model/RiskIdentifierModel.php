<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 账号标识模型
 * 
 * @property int $id
 * @property int $risk_user_id 风险用户ID
 * @property string $app 应用标识
 * @property string $type 标识类型：phone/payment_account/google_id/facebook_business_id
 * @property string $value 标识值
 * @property int $created_at 创建时间（秒级时间戳）
 */
class RiskIdentifierModel extends Model
{
    protected $table = 't_risk_identifier';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // 标识类型常量
    const TYPE_PHONE = 'phone';
    const TYPE_PAYMENT_ACCOUNT = 'payment_account';
    const TYPE_GOOGLE_ID = 'google_id';
    const TYPE_FACEBOOK_BUSINESS_ID = 'facebook_business_id';

    protected $fillable = [
        'risk_user_id',
        'app',
        'type',
        'value',
        'created_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'risk_user_id' => 'integer',
        'created_at' => 'integer',
    ];

    /**
     * 关联风险用户
     */
    public function riskUser()
    {
        return $this->belongsTo(RiskUserModel::class, 'risk_user_id', 'id');
    }

    /**
     * 根据类型和值查找风险用户ID
     */
    public static function findRiskUserIdByTypeValue(string $type, string $value, string $app): ?int
    {
        $record = self::where('type', $type)
            ->where('value', $value)
            ->where('app', $app)
            ->first(['risk_user_id']);
        
        return $record ? $record->risk_user_id : null;
    }

    /**
     * 根据类型和值查找（不限App）
     */
    public static function findRiskUserIdByTypeValueAnyApp(string $type, string $value): ?int
    {
        $record = self::where('type', $type)
            ->where('value', $value)
            ->first(['risk_user_id']);
        
        return $record ? $record->risk_user_id : null;
    }

    /**
     * 创建或更新标识
     */
    public static function createOrUpdate(int $riskUserId, string $app, string $type, string $value): self
    {
        $identifier = self::where('type', $type)
            ->where('value', $value)
            ->where('app', $app)
            ->first();

        if ($identifier) {
            // 更新关联的风险用户
            if ($identifier->risk_user_id !== $riskUserId) {
                $identifier->risk_user_id = $riskUserId;
                $identifier->save();
            }
            return $identifier;
        }

        return self::create([
            'risk_user_id' => $riskUserId,
            'app' => $app,
            'type' => $type,
            'value' => $value,
            'created_at' => time(),
        ]);
    }

    /**
     * 更新标识关联的风险用户
     */
    public function updateRiskUserId(int $newRiskUserId): bool
    {
        $this->risk_user_id = $newRiskUserId;
        return $this->save();
    }

    /**
     * 获取所有支持的标识类型
     */
    public static function getSupportedTypes(): array
    {
        return [
            self::TYPE_PHONE,
            self::TYPE_PAYMENT_ACCOUNT,
            self::TYPE_GOOGLE_ID,
            self::TYPE_FACEBOOK_BUSINESS_ID,
        ];
    }
}
