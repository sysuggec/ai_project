<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\DeletedFileModel;
use App\Models\FileModel;
use App\Models\DirectoryModel;
use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

/**
 * 回收站服务
 * 
 * 管理文件删除、恢复和自动清理功能
 */
class TrashService
{
    /**
     * 回收站文件保留天数
     */
    private const RETENTION_DAYS = 30;

    /**
     * 获取回收站文件列表
     *
     * @return array 回收站文件列表
     */
    public function getList(): array
    {
        $deletedFiles = DeletedFileModel::orderBy('deleted_at', 'desc')->get();

        return $deletedFiles->map(function ($file) {
            $deletedAt = $file->deleted_at;
            $expiresAt = $file->expires_at;
            
            if ($deletedAt instanceof \DateTimeInterface) {
                $deletedAt = $deletedAt->format('Y-m-d H:i:s');
            }
            
            if ($expiresAt instanceof \DateTimeInterface) {
                $expiresAt = $expiresAt->format('Y-m-d H:i:s');
            }

            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'original_directory_path' => $file->original_directory_path ?? '/',
                'file_size' => $file->file_size,
                'deleted_at' => $deletedAt,
                'expires_at' => $expiresAt,
            ];
        })->toArray();
    }

    /**
     * 将文件移动到回收站（软删除）
     *
     * @param int $fileId 文件ID
     * @return bool 是否成功
     * @throws \InvalidArgumentException 当文件不存在时
     */
    public function softDelete(int $fileId): bool
    {
        $file = FileModel::find($fileId);
        
        if (!$file) {
            throw new \InvalidArgumentException('文件不存在');
        }

        // 检查是否已在回收站
        $existingDeleted = DeletedFileModel::where('file_id', $fileId)->first();
        if ($existingDeleted) {
            throw new \RuntimeException('文件已在回收站中');
        }

        // 计算过期时间
        $expiresAt = Carbon::now()->addDays(self::RETENTION_DAYS);

        // 获取原目录路径
        $directory = DirectoryModel::find($file->directory_id);
        $directoryPath = $directory ? $directory->path : '/';

        // 保存到回收站
        DeletedFileModel::create([
            'file_id' => $file->id,
            'file_name' => $file->name,
            'original_directory_id' => $file->directory_id,
            'original_directory_path' => $directoryPath,
            'file_size' => $file->size,
            'hash' => $file->hash,
            'storage_path' => $file->storage_path,
            'mime_type' => $file->mime_type,
            'deleted_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        // 删除原文件记录
        $file->delete();

        return true;
    }

    /**
     * 从回收站恢复文件
     *
     * @param int $deletedFileId 回收站记录ID
     * @return bool 是否成功
     * @throws \InvalidArgumentException 当回收站记录不存在时
     * @throws \RuntimeException 当恢复失败时
     */
    public function restore(int $deletedFileId): bool
    {
        $deletedFile = DeletedFileModel::find($deletedFileId);
        
        if (!$deletedFile) {
            throw new \InvalidArgumentException('回收站记录不存在');
        }

        // 检查原目录是否存在
        $directory = DirectoryModel::find($deletedFile->original_directory_id);
        
        if (!$directory) {
            // 原目录不存在，恢复到根目录
            $rootDir = DirectoryModel::firstOrCreate(
                ['path' => '/'],
                ['name' => '根目录', 'parent_id' => null]
            );
            $targetDirectoryId = $rootDir->id;
        } else {
            $targetDirectoryId = $deletedFile->original_directory_id;
        }

        // 检查目标目录是否存在同名文件
        $existingFile = FileModel::where('directory_id', $targetDirectoryId)
            ->where('name', $deletedFile->file_name)
            ->first();

        if ($existingFile) {
            throw new \RuntimeException('原位置已存在同名文件');
        }

        $connection = DB::connection();
        $connection->beginTransaction();
        try {
            // 恢复文件记录
            FileModel::create([
                'name' => $deletedFile->file_name,
                'hash' => $deletedFile->hash,
                'size' => $deletedFile->file_size,
                'mime_type' => $deletedFile->mime_type,
                'directory_id' => $targetDirectoryId,
                'storage_path' => $deletedFile->storage_path,
                'upload_time' => Carbon::now(),
            ]);

            // 删除回收站记录
            $deletedFile->delete();

            $connection->commit();
            return true;
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw new \RuntimeException('恢复文件失败: ' . $e->getMessage());
        }
    }

    /**
     * 彻底删除文件
     *
     * @param int $deletedFileId 回收站记录ID
     * @return bool 是否成功
     * @throws \InvalidArgumentException 当回收站记录不存在时
     */
    public function deletePermanently(int $deletedFileId): bool
    {
        $deletedFile = DeletedFileModel::find($deletedFileId);
        
        if (!$deletedFile) {
            throw new \InvalidArgumentException('回收站记录不存在');
        }

        // 检查是否有其他文件引用相同的存储路径
        $otherFiles = FileModel::where('hash', $deletedFile->hash)
            ->where('storage_path', $deletedFile->storage_path)
            ->exists();

        // 如果没有其他文件引用，删除物理文件
        if (!$otherFiles) {
            if (file_exists($deletedFile->storage_path)) {
                unlink($deletedFile->storage_path);
            }
        }

        // 删除回收站记录
        return $deletedFile->delete() > 0;
    }

    /**
     * 清空回收站
     *
     * @return int 删除的文件数量
     */
    public function clear(): int
    {
        $deletedFiles = DeletedFileModel::all();
        $count = 0;

        foreach ($deletedFiles as $deletedFile) {
            try {
                $this->deletePermanently($deletedFile->id);
                $count++;
            } catch (\Throwable $e) {
                // 记录错误但继续处理其他文件
                error_log("清空回收站失败 ID {$deletedFile->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * 自动清理过期文件
     *
     * @return int 清理的文件数量
     */
    public function autoCleanup(): int
    {
        $expiredFiles = DeletedFileModel::where('expires_at', '<', Carbon::now())->get();
        $count = 0;

        foreach ($expiredFiles as $expiredFile) {
            try {
                $this->deletePermanently($expiredFile->id);
                $count++;
            } catch (\Throwable $e) {
                // 记录错误但继续处理其他文件
                error_log("自动清理失败 ID {$expiredFile->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
