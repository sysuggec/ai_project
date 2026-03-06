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

        if ($parentPath !== '/' && $parentPath !== '.') {
            $parent = DirectoryModel::where('path', $parentPath)->first();
            $parentId = $parent?->id;
        }

        return DirectoryModel::create([
            'name' => $name,
            'path' => $path,
            'parent_id' => $parentId,
        ]);
    }
}
