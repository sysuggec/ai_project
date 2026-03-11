<?php
declare(strict_types=1);

use App\Controllers\UploadController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;
use App\Controllers\TrashController;
use App\Controllers\ShareController;

$uploadController = new UploadController();
$fileController = new FileController();
$directoryController = new DirectoryController();
$trashController = new TrashController();
$shareController = new ShareController();

$app->post('/api/upload/check', [$uploadController, 'check']);
$app->post('/api/upload/file', [$uploadController, 'uploadFile']);
$app->post('/api/upload/folder', [$uploadController, 'uploadFolder']);
$app->get('/api/upload/history', [$uploadController, 'getHistory']);

$app->get('/api/files', [$fileController, 'index']);
$app->get('/api/files/{id}/download', [$fileController, 'download']);
$app->delete('/api/files/{id}', [$fileController, 'delete']);
$app->put('/api/files/{id}', [$fileController, 'rename']);
$app->put('/api/files/{id}/content', [$fileController, 'updateContent']);
$app->put('/api/files/{id}/move', [$fileController, 'move']);

// 批量操作 - v1.1.0
$app->post('/api/files/batch-delete', [$fileController, 'batchDelete']);
$app->post('/api/files/batch-move', [$fileController, 'batchMove']);

$app->get('/api/directories', [$directoryController, 'index']);
$app->post('/api/directories', [$directoryController, 'create']);

// 回收站 - v1.1.0
$app->get('/api/trash', [$trashController, 'index']);
$app->post('/api/trash/{id}/restore', [$trashController, 'restore']);
$app->delete('/api/trash/{id}', [$trashController, 'deletePermanently']);
$app->delete('/api/trash', [$trashController, 'clear']);

// 文件分享 - v1.1.0
$app->post('/api/shares', [$shareController, 'create']);
$app->get('/api/shares', [$shareController, 'index']);
$app->get('/api/shares/{token}', [$shareController, 'show']);
$app->get('/api/shares/{token}/download', [$shareController, 'download']);
$app->delete('/api/shares/{id}', [$shareController, 'delete']);
