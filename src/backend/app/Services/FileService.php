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

        $otherFiles = FileModel::where('hash', $file->hash)
            ->where('id', '!=', $id)
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
}
