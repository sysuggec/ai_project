<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 文件分享模型
 * 
 * 管理文件分享链接和访问权限
 */
class ShareModel extends Model
{
    protected $table = 'shares';
    
    protected $fillable = [
        'file_id',
        'token',
        'password',
        'expires_at',
        'download_count',
    ];
    
    protected $casts = [
        'file_id' => 'integer',
        'download_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * 关联到文件
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id');
    }

    /**
     * 检查分享是否已过期
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }
        
        return now()->isAfter($this->expires_at);
    }

    /**
     * 检查是否需要密码
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * 验证密码
     */
    public function verifyPassword(string $password): bool
    {
        if (!$this->hasPassword()) {
            return true;
        }
        
        return password_verify($password, $this->password);
    }

    /**
     * 增加下载次数
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }
}
