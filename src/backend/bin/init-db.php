<?php
/**
 * 数据库初始化脚本
 * 用于 Docker 容器启动时初始化数据库
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "Initializing database...\n";

try {
    new Database();
    echo "Database initialized successfully.\n";
} catch (\Throwable $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
    exit(1);
}
