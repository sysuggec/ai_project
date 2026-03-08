# 资源上传下载系统 - 部署文档

## 1. 环境要求

### 1.1 开发环境
- PHP 8.0+
- Composer
- Node.js 16+ & npm/pnpm
- SQLite3 扩展

### 1.2 生产环境
- PHP 8.0+
- Nginx 或 Apache
- SQLite3 扩展
-Composer

---

## 2. 本地开发部署

### 2.1 后端启动

```bash
# 进入后端目录
cd src/backend

# 安装依赖
composer install

# 启动 PHP 内置服务器
php -S 0.0.0.0:8080 -t public
```

服务器将在 `http://0.0.0.0:8080` 启动，可通过局域网 IP 访问。

### 2.2 前端启动

```bash
# 进入前端目录
cd src/frontend

# 安装依赖
npm install

# 开发模式启动
npm run dev -- --host 0.0.0.0
```

前端开发服务器将在 `http://0.0.0.0:5173` 启动。

### 2.3 前端构建

```bash
# 构建生产版本
npm run build

# 构建产物在 dist/ 目录
```

---

## 3. Docker 一键部署

### 3.1 快速开始

```bash
# 默认配置启动（端口 8080）
./deploy.sh start

# 自定义端口和存储目录
./deploy.sh start -p 9000 -u /data/upload -d /data/db

# 其他命令
./deploy.sh stop       # 停止服务
./deploy.sh restart    # 重启服务
./deploy.sh rebuild    # 重新构建并启动
./deploy.sh reset      # 重置所有数据并重新部署
./deploy.sh logs       # 查看日志
./deploy.sh status     # 查看状态
./deploy.sh clean      # 清理容器和镜像
```

### 3.2 构建流程说明

Docker 镜像采用多阶段构建：

1. **前端构建阶段**：Node.js 环境下构建 Vue 应用，输出到 `../backend/public`
2. **后端构建阶段**：PHP-FPM 环境下安装 Composer 依赖
3. **最终镜像**：复制后端代码和前端构建产物

**注意事项**：
- `.dockerignore` 排除了 `src/backend/public/assets/`，避免本地旧构建产物残留
- `index.php` 会被单独复制，确保 PHP 入口文件存在
- 前端构建产物从 `/app/backend/public` 复制，而非 `/app/frontend/dist`

### 3.3 数据持久化注意事项

**重要**：Docker 部署使用卷映射持久化数据，这意味着：

- 重新构建镜像（`deploy.sh rebuild`）不会清除数据库和上传文件
- 旧数据可能包含脏数据，导致目录树显示异常
- 如需全新部署，请使用 `deploy.sh reset` 命令

```bash
# 重置所有数据（清理数据库和上传文件）
./deploy.sh reset
```

### 3.3 环境配置

复制 `.env.docker` 为 `.env` 并根据需要修改：

```env
# 宿主机端口映射
HOST_PORT=8080

# 上传文件存储路径（宿主机路径）
UPLOAD_PATH=./data/upload

# 数据库存储路径（宿主机路径）
DB_PATH=./data/db

# PHP 上传配置（0 表示无限制）
PHP_UPLOAD_MAX_FILESIZE=0
PHP_POST_MAX_SIZE=0
PHP_MAX_EXECUTION_TIME=0
```

### 3.4 数据卷映射

Docker 部署通过卷映射实现数据持久化：

| 容器路径 | 默认宿主机路径 | 说明 |
|---------|--------------|------|
| `/data/upload` | `./data/upload` | 上传文件存储 |
| `/var/www/html/storage` | `./data/db` | SQLite 数据库 |

### 3.5 Docker Compose 手动操作

```bash
# 构建并启动
docker compose up -d

# 查看日志
docker compose logs -f

# 停止服务
docker compose down

# 重新构建
docker compose build --no-cache
```

---

## 4. 生产环境部署

### 4.1 Nginx 配置

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/resource-system/src/backend/public;
    index index.php;

    # API 请求转发到 PHP
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 前端静态文件
    location / {
        root /var/www/resource-system/src/frontend/dist;
        try_files $uri $uri/ /index.html;
    }

    # 上传文件存储目录
    location /uploads {
        alias /data/upload;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 4.2 目录权限

```bash
# 创建上传目录
sudo mkdir -p /data/upload
sudo chown -R www-data:www-data /data/upload
sudo chmod -R 755 /data/upload

# 创建存储目录
sudo mkdir -p src/backend/storage
sudo chown -R www-data:www-data src/backend/storage
sudo chmod -R 755 src/backend/storage
```

---

## 5. 数据库初始化

首次运行时，系统会自动创建数据库表。如需手动初始化：

```bash
cd src/backend
php bin/init-db.php
```

---

## 6. 配置说明

### 6.1 后端配置 (src/backend/config/app.php)

```php
<?php
return [
    'upload_path' => '/data/upload',
    'max_file_size' => 0, // 0 表示无限制
    'allowed_types' => [], // 空数组表示允许所有类型
];
```

### 6.2 前端配置 (src/frontend/.env)

```env
VITE_API_BASE_URL=http://your-server-ip:8080/api
```

---

## 7. 一键启动脚本

### 7.1 开发环境启动 (start-dev.sh)

```bash
#!/bin/bash

# 启动后端
cd src/backend
php -S 0.0.0.0:8080 -t public &
BACKEND_PID=$!

# 启动前端
cd ../frontend
npm run dev -- --host 0.0.0.0 &
FRONTEND_PID=$!

echo "后端 PID: $BACKEND_PID"
echo "前端 PID: $FRONTEND_PID"
echo "按 Ctrl+C 停止服务"

trap "kill $BACKEND_PID $FRONTEND_PID" EXIT
wait
```

---

## 8. 常见问题

### Q: 上传大文件失败？
A: 修改 `php.ini`：
```ini
upload_max_filesize = 0
post_max_size = 0
max_execution_time = 0
max_input_time = 0
```

### Q: 跨域问题？
A: 后端已配置 CORS 中间件，允许所有来源访问。

### Q: 数据库锁定？
A: SQLite 在高并发下可能锁定，生产环境建议切换到 MySQL/PostgreSQL。

### Q: Docker 构建后前端代码不是最新版本？
A: 这通常是因为本地 `src/backend/public/assets/` 目录存在旧的构建产物。解决方案：
1. 使用 `deploy.sh rebuild` 强制重新构建
2. 或手动清理：`rm -rf src/backend/public/assets/*`，然后重新构建
3. `.dockerignore` 已配置排除旧文件，确保该文件存在

### Q: Docker 部署后目录树显示异常？
A: 可能是数据库中存在脏数据。使用以下步骤重置：
```bash
./deploy.sh reset   # 清理数据并重新部署
```

### Q: 秒传功能不可用？
A: 秒传依赖 Web Crypto API，需要安全上下文：
- ✅ HTTPS 访问
- ✅ localhost / 127.0.0.1 访问
- ❌ HTTP（非本地）访问时秒传不可用，但上传功能正常

**解决方案**：
1. 生产环境配置 HTTPS（推荐）
2. 开发环境使用 localhost 访问
3. 如需局域网访问秒传功能，配置自签名证书

---

## 9. 通过 IP 访问

开发模式下，服务器绑定 `0.0.0.0`，可通过以下方式访问：

- 本机：`http://localhost:8080`
- 局域网：`http://192.168.x.x:8080`

查看本机 IP：
```bash
# Linux/Mac
ip addr show
# 或
ifconfig
```
