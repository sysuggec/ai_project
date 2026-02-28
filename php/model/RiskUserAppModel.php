<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * App维度用户信息模型
 * 
 * @property int $id
 * @property int $risk_user_id 风险用户ID
 * @property string $app 应用标识
 * @property string $uid App内用户UID
 * @property string $nickname 用户昵称
 * @property int $register_time 注册时间（秒级时间戳）
 * @property string $register_ip 注册IP
 * @property string $google_nickname Google账号昵称
 * @property string $facebook_nickname Facebook账号昵称
 * @property int $linked_at 关联时间（秒级时间戳）
 * @property int $created_at 创建时间（秒级时间戳）
 * @property int $updated_at 更新时间（秒级时间戳）
 */
class RiskUserAppModel extends Model
{
    protected $table = 't_risk_user_app';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'risk_user_id',
        'app',
        'uid',
        'nickname',
        'register_time',
        'register_ip',
        'google_nickname',
        'facebook_nickname',
        'linked_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'risk_user_id' => 'integer',
        'register_time' => 'integer',
        'linked_at' => 'integer',
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
     * 创建或更新App用户信息
     */
    public static function createOrUpdate(int $riskUserId, string $app, array $data): self
    {
        $now = time();
        $record = self::where('risk_user_id', $riskUserId)
            ->where('app', $app)
            ->first();

        if ($record) {
            // 更新已有记录
            $updateData = ['updated_at' => $now];
            
            if (isset($data['uid'])) {
                $updateData['uid'] = $data['uid'];
            }
            if (isset($data['nickname'])) {
                $updateData['nickname'] = $data['nickname'];
            }
            if (isset($data['register_time'])) {
                $updateData['register_time'] = $data['register_time'];
            }
            if (isset($data['register_ip'])) {
                $updateData['register_ip'] = $data['register_ip'];
            }
            if (isset($data['google_nickname'])) {
                $updateData['google_nickname'] = $data['google_nickname'];
            }
            if (isset($data['facebook_nickname'])) {
                $updateData['facebook_nickname'] = $data['facebook_nickname'];
            }

            $record->fill($updateData);
            $record->save();
            
            return $record;
        }

        // 创建新记录
        return self::create([
            'risk_user_id' => $riskUserId,
            'app' => $app,
            'uid' => $data['uid'] ?? '',
            'nickname' => $data['nickname'] ?? '',
            'register_time' => $data['register_time'] ?? 0,
            'register_ip' => $data['register_ip'] ?? '',
            'google_nickname' => $data['google_nickname'] ?? '',
            'facebook_nickname' => $data['facebook_nickname'] ?? '',
            'linked_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
