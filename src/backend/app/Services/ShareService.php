<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ShareModel;
use App\Models\FileModel;
use Illuminate\Support\Str;

/**
 * 文件分享服务
 * 
 * 管理文件分享链接的创建、验证和访问
 */
class ShareService
{
    /**
     * Token 长度
     */
    private const TOKEN_LENGTH = 32;

    /**
     * 默认过期时间（秒）- 24小时
     */
    private const DEFAULT_EXPIRES_IN = 86400;

    /**
     * 创建分享链接
     *
     * @param int $fileId 文件ID
     * @param int $expiresIn 过期时间（秒）
     * @param string|null $password 访问密码（可选）
     * @return array 分享信息
     * @throws \InvalidArgumentException 当文件不存在时
     */
    public function create(int $fileId, int $expiresIn = self::DEFAULT_EXPIRES_IN, ?string $password = null): array
    {
        // 验证文件是否存在
        $file = FileModel::find($fileId);
        
        if (!$file) {
            throw new \InvalidArgumentException('文件不存在');
        }

        // 生成唯一 token
        $token = $this->generateToken();

        // 计算过期时间
        $expiresAt = now()->addSeconds($expiresIn);

        // 加密密码（如果有）
        $hashedPassword = null;
        if ($password !== null && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }

        // 创建分享记录
        $share = ShareModel::create([
            'file_id' => $fileId,
            'token' => $token,
            'password' => $hashedPassword,
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $share->token,
            'share_url' => '/share/' . $share->token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'has_password' => $hashedPassword !== null,
        ];
    }

    /**
     * 通过 token 获取分享文件信息
     *
     * @param string $token 分享 token
     * @param string|null $password 访问密码
     * @return array 文件信息
     * @throws \InvalidArgumentException 当分享不存在或已过期时
     * @throws \RuntimeException 当密码错误时
     */
    public function getByToken(string $token, ?string $password = null): array
    {
        $share = ShareModel::where('token', $token)->first();

        if (!$share) {
            throw new \InvalidArgumentException('分享链接不存在');
        }

        // 检查是否过期
        if ($share->isExpired()) {
            throw new \RuntimeException('分享链接已过期');
        }

        // 验证密码
        if ($share->hasPassword()) {
            if ($password === null || !$share->verifyPassword($password)) {
                throw new \RuntimeException('密码错误');
            }
        }

        // 获取文件信息
        $file = FileModel::find($share->file_id);
        
        if (!$file) {
            throw new \InvalidArgumentException('文件已被删除');
        }

        return [
            'file_name' => $file->name,
            'file_size' => $file->size,
            'mime_type' => $file->mime_type,
            'download_url' => '/api/shares/' . $token . '/download',
            'has_password' => $share->hasPassword(),
        ];
    }

    /**
     * 获取分享文件下载路径
     *
     * @param string $token 分享 token
     * @param string|null $password 访问密码
     * @return array 文件路径信息
     * @throws \InvalidArgumentException 当分享不存在或已过期时
     * @throws \RuntimeException 当密码错误时
     */
    public function getDownloadPath(string $token, ?string $password = null): array
    {
        $share = ShareModel::where('token', $token)->first();

        if (!$share) {
            throw new \InvalidArgumentException('分享链接不存在');
        }

        // 检查是否过期
        if ($share->isExpired()) {
            throw new \RuntimeException('分享链接已过期');
        }

        // 验证密码
        if ($share->hasPassword()) {
            if ($password === null || !$share->verifyPassword($password)) {
                throw new \RuntimeException('密码错误');
            }
        }

        // 获取文件信息
        $file = FileModel::find($share->file_id);
        
        if (!$file) {
            throw new \InvalidArgumentException('文件已被删除');
        }

        // 增加下载次数
        $share->incrementDownloadCount();

        return [
            'path' => $file->storage_path,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
        ];
    }

    /**
     * 获取用户的分享列表
     *
     * @return array 分享列表
     */
    public function getList(): array
    {
        $shares = ShareModel::with('file')->orderBy('created_at', 'desc')->get();

        return $shares->map(function ($share) {
            $expiresAt = $share->expires_at;
            $createdAt = $share->created_at;
            
            if ($expiresAt instanceof \DateTimeInterface) {
                $expiresAt = $expiresAt->format('Y-m-d H:i:s');
            }
            
            if ($createdAt instanceof \DateTimeInterface) {
                $createdAt = $createdAt->format('Y-m-d H:i:s');
            }

            return [
                'id' => $share->id,
                'file_id' => $share->file_id,
                'file_name' => $share->file ? $share->file->name : '已删除',
                'token' => $share->token,
                'expires_at' => $expiresAt,
                'download_count' => $share->download_count,
                'created_at' => $createdAt,
                'has_password' => $share->hasPassword(),
            ];
        })->toArray();
    }

    /**
     * 删除分享
     *
     * @param int $id 分享ID
     * @return bool 是否成功
     * @throws \InvalidArgumentException 当分享不存在时
     */
    public function delete(int $id): bool
    {
        $share = ShareModel::find($id);

        if (!$share) {
            throw new \InvalidArgumentException('分享不存在');
        }

        return $share->delete() > 0;
    }

    /**
     * 生成唯一 token
     *
     * @return string token 字符串
     */
    private function generateToken(): string
    {
        do {
            $token = Str::random(self::TOKEN_LENGTH);
        } while (ShareModel::where('token', $token)->exists());

        return $token;
    }
}
