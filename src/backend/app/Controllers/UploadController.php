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

        if (!$file) {
            return Response::error('No file uploaded');
        }

        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();
        $mimeType = $file->getMimeType();
        $hash = $request->request->get('hash');
        $directory = $request->request->get('directory', '/');

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
                $file->getPathname()
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
            return Response::error($e->getMessage());
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

            $hash = hash_file('sha256', $file->getPathname());

            try {
                $uploadedFile = $this->uploadService->uploadFile(
                    $file->getClientOriginalName(),
                    $hash,
                    $file->getSize(),
                    $file->getMimeType(),
                    $targetDirectory,
                    $file->getPathname()
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
