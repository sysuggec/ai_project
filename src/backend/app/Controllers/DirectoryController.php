<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\DirectoryService;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController
{
    private DirectoryService $directoryService;

    public function __construct()
    {
        $this->directoryService = new DirectoryService();
    }

    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $tree = $this->directoryService->getDirectoryTree();

        return Response::success(['directories' => $tree]);
    }

    public function create(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $path = $data['path'] ?? '';

        if (empty($path)) {
            return Response::error('Path is required');
        }

        $directory = $this->directoryService->createDirectory($path);

        return Response::success([
            'directory' => [
                'id' => $directory->id,
                'name' => $directory->name,
                'path' => $directory->path,
            ],
        ]);
    }
}
