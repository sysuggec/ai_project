<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\FileModel;
use App\Models\DirectoryModel;
use App\Models\UploadHistoryModel;
use Illuminate\Database\Capsule\Manager as Capsule;

class UploadService
{
    private string $uploadPath;
    private int $hashLevels;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $this->uploadPath = $config['upload_path'];
        $this->hashLevels = $config['hash_storage_levels'];
    }

    public function checkHash(string $hash): array
    {
        $file = FileModel::where('hash', $hash)->first();

        return [
            'exists' => $file !== null,
            'file' => $file,
        ];
    }

    public function uploadFile(
        string $originalName,
        string $hash,
        int $size,
        string $mimeType,
        string $directoryPath,
        ?string $tempPath = null
    ): FileModel {
        $connection = Capsule::connection();
        $connection->beginTransaction();

        try {
            $directory = $this->getOrCreateDirectory($directoryPath);

            $existingFile = FileModel::where('hash', $hash)
                ->where('directory_id', $directory->id)
                ->where('name', $originalName)
                ->first();

            if ($existingFile) {
                $connection->commit();
                return $existingFile;
            }

            $storagePath = $this->getStoragePath($hash);

            if ($tempPath !== null && !file_exists($storagePath)) {
                $dir = dirname($storagePath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                move_uploaded_file($tempPath, $storagePath);
            }

            $file = FileModel::create([
                'name' => $originalName,
                'hash' => $hash,
                'size' => $size,
                'mime_type' => $mimeType,
                'directory_id' => $directory->id,
                'storage_path' => $storagePath,
                'upload_time' => date('Y-m-d H:i:s'),
            ]);

            $this->recordHistory($file, $directoryPath, $tempPath === null);

            $connection->commit();
            return $file;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function getOrCreateDirectory(string $path): DirectoryModel
    {
        // 处理根目录
        if ($path === '/' || $path === '') {
            $directory = DirectoryModel::where('path', '/')->first();
            if ($directory) {
                return $directory;
            }
        return DirectoryModel::create([
            'name' => '根目录',
            'path' => '/',
            'parent_id' => null,
        ]);
        }

        $directory = DirectoryModel::where('path', $path)->first();

        if ($directory) {
            return $directory;
        }

        $parts = array_filter(explode('/', trim($path, '/')));
        $parent = null;
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            $existing = DirectoryModel::where('path', $currentPath)->first();

            if ($existing) {
                $parent = $existing;
            } else {
                $parent = DirectoryModel::create([
                    'name' => $part,
                    'path' => $currentPath,
                    'parent_id' => $parent?->id,
                ]);
            }
        }

        return $parent;
    }

    private function getStoragePath(string $hash): string
    {
        $prefix = substr($hash, 0, $this->hashLevels);
        $dir = $this->uploadPath . '/' . $prefix;

        return $dir . '/' . $hash;
    }

    private function recordHistory(FileModel $file, string $directory, bool $isInstant): void
    {
        UploadHistoryModel::create([
            'file_id' => $file->id,
            'original_name' => $file->name,
            'target_directory' => $directory,
            'file_size' => $file->size,
            'hash' => $file->hash,
            'is_instant_upload' => $isInstant,
            'status' => 'success',
            'upload_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
