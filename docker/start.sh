#!/bin/sh
set -e

echo "=== Starting Resource System ==="

# 设置 PHP 配置
if [ -n "$PHP_UPLOAD_MAX_FILESIZE" ]; then
    echo "upload_max_filesize = $PHP_UPLOAD_MAX_FILESIZE" > /usr/local/etc/php/conf.d/uploads.ini
fi
if [ -n "$PHP_POST_MAX_SIZE" ]; then
    echo "post_max_size = $PHP_POST_MAX_SIZE" >> /usr/local/etc/php/conf.d/uploads.ini
fi
if [ -n "$PHP_MAX_EXECUTION_TIME" ]; then
    echo "max_execution_time = $PHP_MAX_EXECUTION_TIME" >> /usr/local/etc/php/conf.d/uploads.ini
fi

# 创建数据库文件（如果不存在）
DB_FILE="/var/www/html/storage/database.sqlite"
if [ ! -f "$DB_FILE" ]; then
    echo "Creating database file..."
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
    chmod 644 "$DB_FILE"
fi

# 初始化数据库表
echo "Initializing database tables..."
cd /var/www/html
php bin/init-db.php

# 确保目录权限
chown -R www-data:www-data /data/upload
chown -R www-data:www-data /var/www/html/storage
chmod -R 755 /data/upload
chmod -R 755 /var/www/html/storage

echo "=== Starting PHP-FPM and Nginx ==="

# 启动 PHP-FPM（后台）
php-fpm -D

# 启动 Nginx（前台）
exec nginx -g 'daemon off;'
