<?php
declare(strict_types=1);

use App\Core\Application;
use App\Core\Database;
use App\Core\Response;
use App\Middleware\CorsMiddleware;

// 设置 PHP 时区（与 Docker TZ 环境变量保持一致）
date_default_timezone_set(getenv('TZ') ?: 'Asia/Shanghai');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    http_response_code(204);
    exit;
}

// API 请求
if (str_starts_with($requestPath, '/api')) {
    $app = new Application();
    $db = new Database();
    $app->use(new CorsMiddleware());
    require_once __DIR__ . '/../routes/api.php';
    $app->run();
    exit;
}

// 静态资源文件
$publicDir = __DIR__;
if (preg_match('#^/assets/(.+)$#', $requestPath, $matches)) {
    $file = $publicDir . '/assets/' . $matches[1];
    if (file_exists($file) && is_file($file)) {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($file);
        exit;
    }
}

// SPA 路由 - 返回 index.html
$indexPath = $publicDir . '/index.html';
if (file_exists($indexPath)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($indexPath);
    exit;
}

http_response_code(404);
echo 'Not Found';
