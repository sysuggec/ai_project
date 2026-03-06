<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\FileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $directory = $request->query->get('directory');
        $search = $request->query->get('search');

        $files = $this->fileService->getFiles($directory, $search);

        return Response::success(['files' => $files]);
    }

    public function download(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $fileInfo = $this->fileService->getFilePath((int) $id);

        if (!$fileInfo) {
            return Response::error('File not found', 404);
        }

        if (!file_exists($fileInfo['path'])) {
            return Response::error('File not found on disk', 404);
        }

        $response = new BinaryFileResponse($fileInfo['path']);
        $response->setContentDisposition('attachment', $fileInfo['name']);
        $response->headers->set('Content-Type', $fileInfo['mime_type'] ?: 'application/octet-stream');

        return $response;
    }

    public function delete(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $success = $this->fileService->deleteFile((int) $id);

        if (!$success) {
            return Response::error('File not found', 404);
        }

        return Response::success(['deleted' => true]);
    }

    public function rename(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $newName = $data['name'] ?? '';

        if (empty($newName)) {
            return Response::error('Name is required');
        }

        $file = $this->fileService->renameFile((int) $id, $newName);

        if (!$file) {
            return Response::error('File not found', 404);
        }

        return Response::success([
            'file' => [
                'id' => $file->id,
                'name' => $file->name,
            ],
        ]);
    }
}
