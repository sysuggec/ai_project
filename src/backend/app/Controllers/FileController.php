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

    public function updateContent(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? '';

        $file = $this->fileService->updateFileContent((int) $id, $content);

        if (!$file) {
            return Response::error('File not found or cannot be updated', 404);
        }

        return Response::success([
            'file' => [
                'id' => $file->id,
                'name' => $file->name,
                'size' => $file->size,
            ],
        ]);
    }

    /**
     * 移动文件到目标目录
     */
    public function move(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $targetDirectoryId = $data['target_directory_id'] ?? null;

        if ($targetDirectoryId === null) {
            return Response::error('目标目录ID是必需的');
        }

        try {
            $file = $this->fileService->move((int) $id, (int) $targetDirectoryId);
            return Response::success(['file' => $file]);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            return Response::error('移动文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 批量删除文件
     */
    public function batchDelete(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $fileIds = $data['file_ids'] ?? [];

        if (empty($fileIds) || !is_array($fileIds)) {
            return Response::error('文件ID数组不能为空');
        }

        $result = $this->fileService->batchDelete($fileIds);

        return Response::success($result);
    }

    /**
     * 批量移动文件
     */
    public function batchMove(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $fileIds = $data['file_ids'] ?? [];
        $targetDirectoryId = $data['target_directory_id'] ?? null;

        if (empty($fileIds) || !is_array($fileIds)) {
            return Response::error('文件ID数组不能为空');
        }

        if ($targetDirectoryId === null) {
            return Response::error('目标目录ID是必需的');
        }

        $result = $this->fileService->batchMove($fileIds, (int) $targetDirectoryId);

        return Response::success($result);
    }
}
