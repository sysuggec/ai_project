<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\FileModel;
use App\Models\DirectoryModel;
use Illuminate\Database\Eloquent\Builder;

class FileService
{
    public function getFiles(?string $directoryPath = null, ?string $search = null): array
    {
        $query = FileModel::with('directory');

        if ($directoryPath) {
            $directory = DirectoryModel::where('path', $directoryPath)->first();
            if ($directory) {
                $query->where('directory_id', $directory->id);
            }
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $files = $query->orderBy('created_at', 'desc')->get();

        return $files->map(function ($file) {
            $uploadTime = $file->upload_time;
            if ($uploadTime instanceof \DateTimeInterface) {
                $uploadTime = $uploadTime->format('Y-m-d H:i:s');
            }

            return [
                'id' => $file->id,
                'name' => $file->name,
                'size' => $file->size,
                'mime_type' => $file->mime_type,
                'directory' => $file->directory?->path ?? '/',
                'upload_time' => $uploadTime,
                'storage_path' => $file->storage_path,
            ];
        })->toArray();
    }

    public function deleteFile(int $id): bool
    {
        $file = FileModel::find($id);

        if (!$file) {
            return false;
        }

        // v1.1.0: 改为软删除（移动到回收站）
        $trashService = new TrashService();
        
        try {
            return $trashService->softDelete($id);
        } catch (\Throwable $e) {
            // 如果软删除失败，执行硬删除（向后兼容）
            return $this->hardDelete($file);
        }
    }

    /**
     * 硬删除文件（物理删除）
     *
     * @param FileModel $file 文件模型
     * @return bool 是否成功
     */
    private function hardDelete(FileModel $file): bool
    {
        $otherFiles = FileModel::where('hash', $file->hash)
            ->where('id', '!=', $file->id)
            ->exists();

        if (!$otherFiles) {
            if (file_exists($file->storage_path)) {
                unlink($file->storage_path);
            }
        }

        return $file->delete() > 0;
    }

    public function renameFile(int $id, string $newName): ?FileModel
    {
        $file = FileModel::find($id);

        if (!$file) {
            return null;
        }

        $file->name = $newName;
        $file->save();

        return $file;
    }

    public function getFilePath(int $id): ?array
    {
        $file = FileModel::find($id);

        if (!$file) {
            return null;
        }

        return [
            'path' => $file->storage_path,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
        ];
    }

    public function updateFileContent(int $id, string $content): ?FileModel
    {
        $file = FileModel::find($id);

        if (!$file) {
            return null;
        }

        if (!file_exists($file->storage_path)) {
            return null;
        }

        // 写入新内容
        $bytesWritten = file_put_contents($file->storage_path, $content);
        if ($bytesWritten === false) {
            return null;
        }

        // 更新文件大小
        $file->size = strlen($content);
        $file->save();

        return $file;
    }

    /**
     * 移动文件到目标目录
     *
     * @param int $fileId 要移动的文件ID
     * @param int $targetDirectoryId 目标目录ID
     * @return array 移动后的文件信息
     * @throws \InvalidArgumentException 当参数无效时
     * @throws \RuntimeException 当移动失败时
     */
    public function move(int $fileId, int $targetDirectoryId): array
    {
        // 验证文件是否存在
        $file = FileModel::find($fileId);
        if (!$file) {
            throw new \InvalidArgumentException('文件不存在');
        }

        // 验证目标目录是否存在
        $targetDirectory = DirectoryModel::find($targetDirectoryId);
        if (!$targetDirectory) {
            throw new \InvalidArgumentException('目标目录不存在');
        }

        // 检查是否移动到相同目录
        if ($file->directory_id === $targetDirectoryId) {
            throw new \InvalidArgumentException('文件已在该目录中');
        }

        // 检查目标目录是否存在同名文件
        $existingFile = FileModel::where('directory_id', $targetDirectoryId)
            ->where('name', $file->name)
            ->first();
        
        if ($existingFile) {
            throw new \RuntimeException('目标目录已存在同名文件');
        }

        // 移动文件（更新 directory_id）
        $file->directory_id = $targetDirectoryId;
        $file->save();

        return [
            'id' => $file->id,
            'name' => $file->name,
            'directory_id' => $file->directory_id,
        ];
    }

    /**
     * 批量删除文件（移动到回收站）
     *
     * @param array $fileIds 要删除的文件ID数组
     * @return array 删除结果统计
     */
    public function batchDelete(array $fileIds): array
    {
        $successCount = 0;
        $failedItems = [];

        foreach ($fileIds as $fileId) {
            try {
                $this->deleteFile($fileId);
                $successCount++;
            } catch (\Throwable $e) {
                $failedItems[] = [
                    'file_id' => $fileId,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => count($failedItems),
            'failed_items' => $failedItems,
        ];
    }

    /**
     * 批量移动文件
     *
     * @param array $fileIds 要移动的文件ID数组
     * @param int $targetDirectoryId 目标目录ID
     * @return array 移动结果统计
     */
    public function batchMove(array $fileIds, int $targetDirectoryId): array
    {
        $successCount = 0;
        $failedItems = [];

        foreach ($fileIds as $fileId) {
            try {
                $this->move($fileId, $targetDirectoryId);
                $successCount++;
            } catch (\Throwable $e) {
                $failedItems[] = [
                    'file_id' => $fileId,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => count($failedItems),
            'failed_items' => $failedItems,
        ];
    }
}
