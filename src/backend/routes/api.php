<?php
declare(strict_types=1);

use App\Controllers\UploadController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;

$uploadController = new UploadController();
$fileController = new FileController();
$directoryController = new DirectoryController();

$app->post('/api/upload/check', [$uploadController, 'check']);
$app->post('/api/upload/file', [$uploadController, 'uploadFile']);
$app->post('/api/upload/folder', [$uploadController, 'uploadFolder']);
$app->get('/api/upload/history', [$uploadController, 'getHistory']);

$app->get('/api/files', [$fileController, 'index']);
$app->get('/api/files/{id}/download', [$fileController, 'download']);
$app->delete('/api/files/{id}', [$fileController, 'delete']);
$app->put('/api/files/{id}', [$fileController, 'rename']);
$app->put('/api/files/{id}/content', [$fileController, 'updateContent']);

$app->get('/api/directories', [$directoryController, 'index']);
$app->post('/api/directories', [$directoryController, 'create']);
