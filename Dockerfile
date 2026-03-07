# 多阶段构建

# 阶段1: 前端构建
FROM node:20-alpine AS frontend-builder

WORKDIR /app/frontend

# 复制前端依赖文件
COPY src/frontend/package*.json ./

# 安装依赖
RUN npm install

# 复制前端源码
COPY src/frontend/ ./

# 构建前端（输出到 dist/）
RUN npm run build

# 阶段2: 生产镜像
FROM php:8.2-fpm-alpine

# 安装系统依赖和 PHP 扩展
RUN apk add --no-cache \
    nginx \
    sqlite \
    sqlite-dev \
    curl \
    && docker-php-ext-install pdo_sqlite

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 复制后端代码（排除 public/assets 目录，避免旧前端文件残留）
COPY src/backend/app ./.app-temp
COPY src/backend/config ./config
COPY src/backend/routes ./routes
COPY src/backend/bin ./bin
COPY src/backend/storage ./storage
COPY src/backend/public/index.php ./public/index.php
COPY src/backend/composer.* ./
RUN mv .app-temp app

# 安装 PHP 依赖（生产环境，排除开发依赖）
RUN composer install --no-dev --optimize-autoloader

# 创建 public 目录并复制前端构建产物（Vite 输出到 ../backend/public）
RUN mkdir -p public
COPY --from=frontend-builder /app/backend/public ./public/

# 创建必要的目录
RUN mkdir -p /data/upload \
    && mkdir -p storage \
    && chown -R www-data:www-data /data/upload \
    && chown -R www-data:www-data storage \
    && chmod -R 755 /data/upload \
    && chmod -R 755 storage

# 复制 Nginx 配置
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# 复制启动脚本
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# 暴露端口
EXPOSE 80

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/directories || exit 1

# 启动
CMD ["/start.sh"]
