<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\UploadService;
use App\Models\UploadHistoryModel;
use Symfony\Component\HttpFoundation\Request;

class UploadController
{
    private UploadService $uploadService;

    public function __construct()
    {
        $this->uploadService = new UploadService();
    }

    public function check(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $hash = $data['hash'] ?? '';

        if (empty($hash)) {
            return Response::error('Hash is required');
        }

        $result = $this->uploadService->checkHash($hash);

        return Response::success([
            'exists' => $result['exists'],
        ]);
    }

    public function uploadFile(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $file = $request->files->get('file');
        $hash = $request->request->get('hash');
        $directory = $request->request->get('directory', '/');

        // 秒传模式：只传哈希，不传文件
        if (!$file && $hash) {
            $fileName = $request->request->get('filename', $hash . '.dat');
            
            // 从已存在的文件记录中获取大小和 MIME 类型
            $existingFile = $this->uploadService->checkHash($hash);
            $fileSize = $existingFile['file']?->size ?? 0;
            $mimeType = $existingFile['file']?->mime_type ?? 'application/octet-stream';
            
            try {
                $uploadedFile = $this->uploadService->uploadFile(
                    $fileName,
                    $hash,
                    $fileSize,
                    $mimeType,
                    $directory,
                    null // 无实际文件
                );

                return Response::success([
                    'file' => [
                        'id' => $uploadedFile->id,
                        'name' => $uploadedFile->name,
                        'size' => $uploadedFile->size,
                        'hash' => $uploadedFile->hash,
                    ],
                ]);
            } catch (\Exception $e) {
                return Response::error($e->getMessage(), 500);
            }
        }

        if (!$file) {
            return Response::error('No file uploaded');
        }

        // 检查文件是否有效上传
        if (!$file->isValid()) {
            return Response::error('File upload failed: ' . $file->getErrorMessage());
        }

        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();
        
        // 安全获取 MIME 类型
        $mimeType = $file->getClientMimeType();
        $realPath = $file->getRealPath();
        if ($realPath && file_exists($realPath)) {
            $guessedType = $file->getMimeType();
            if ($guessedType) {
                $mimeType = $guessedType;
            }
        }

        if (empty($hash)) {
            $hash = hash_file('sha256', $file->getPathname());
        }

        try {
            $uploadedFile = $this->uploadService->uploadFile(
                $originalName,
                $hash,
                $size,
                $mimeType,
                $directory,
                $file
            );

            return Response::success([
                'file' => [
                    'id' => $uploadedFile->id,
                    'name' => $uploadedFile->name,
                    'size' => $uploadedFile->size,
                    'hash' => $uploadedFile->hash,
                ],
            ]);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function uploadFolder(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $files = $request->files->all();
        $directory = $request->request->get('directory', '/');
        $results = [];

        foreach ($files as $key => $file) {
            $relativePath = $request->request->get("paths[$key]", '');
            $targetDirectory = $directory . '/' . dirname($relativePath);

            // 检查文件是否有效上传
            if (!$file->isValid()) {
                $results[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $targetDirectory,
                    'status' => 'error',
                    'error' => 'File upload failed: ' . $file->getErrorMessage(),
                ];
                continue;
            }

            $hash = hash_file('sha256', $file->getPathname());
            
            // 安全获取 MIME 类型
            $mimeType = $file->getClientMimeType();
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath)) {
                $guessedType = $file->getMimeType();
                if ($guessedType) {
                    $mimeType = $guessedType;
                }
            }

            try {
                $uploadedFile = $this->uploadService->uploadFile(
                    $file->getClientOriginalName(),
                    $hash,
                    $file->getSize(),
                    $mimeType,
                    $targetDirectory,
                    $file
                );

                $results[] = [
                    'name' => $uploadedFile->name,
                    'path' => $targetDirectory,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $targetDirectory,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return Response::success(['results' => $results]);
    }

    public function getHistory(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        $histories = UploadHistoryModel::orderBy('upload_time', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = UploadHistoryModel::count();

        return Response::success([
            'histories' => $histories->map(function ($h) {
                $uploadTime = $h->upload_time;
                if ($uploadTime instanceof \DateTimeInterface) {
                    $uploadTime = $uploadTime->format('Y-m-d H:i:s');
                }
                
                return [
                    'id' => $h->id,
                    'original_name' => $h->original_name,
                    'target_directory' => $h->target_directory,
                    'file_size' => $h->file_size,
                    'is_instant_upload' => $h->is_instant_upload,
                    'status' => $h->status,
                    'error_message' => $h->error_message,
                    'upload_time' => $uploadTime,
                ];
            }),
            'total' => $total,
        ]);
    }
}
