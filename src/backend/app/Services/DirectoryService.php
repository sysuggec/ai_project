<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\DirectoryModel;

class DirectoryService
{
    public function getDirectoryTree(): array
    {
        $directories = DirectoryModel::orderBy('path')->get();

        return $this->buildTree($directories);
    }

    private function buildTree($directories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($directories as $directory) {
            if ($directory->parent_id === $parentId) {
                $node = [
                    'id' => $directory->id,
                    'name' => $directory->name,
                    'path' => $directory->path,
                    'children' => $this->buildTree($directories, $directory->id),
                ];
                $tree[] = $node;
            }
        }

        return $tree;
    }

    public function createDirectory(string $path): DirectoryModel
    {
        $existing = DirectoryModel::where('path', $path)->first();

        if ($existing) {
            return $existing;
        }

        $name = basename($path);
        $parentPath = dirname($path);
        $parentId = null;

        // 确保根目录存在
        if ($path !== '/') {
            $rootDir = DirectoryModel::firstOrCreate(
                ['path' => '/'],
                ['name' => '根目录', 'parent_id' => null]
            );
            
            // 如果父路径是根目录，使用根目录的 id
            if ($parentPath === '/' || $parentPath === '.') {
                $parentId = $rootDir->id;
            } else {
                // 递归创建父目录
                $parent = $this->createDirectory($parentPath);
                $parentId = $parent->id;
            }
        }

        return DirectoryModel::create([
            'name' => $name ?: '根目录',
            'path' => $path,
            'parent_id' => $parentId,
        ]);
    }
}
