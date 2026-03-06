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

## 3. 生产环境部署

### 3.1 Nginx 配置

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

### 3.2 目录权限

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

## 4. 数据库初始化

首次运行时，系统会自动创建数据库表。如需手动初始化：

```bash
cd src/backend
php bin/init-db.php
```

---

## 5. 配置说明

### 5.1 后端配置 (src/backend/config/app.php)

```php
<?php
return [
    'upload_path' => '/data/upload',
    'max_file_size' => 0, // 0 表示无限制
    'allowed_types' => [], // 空数组表示允许所有类型
];
```

### 5.2 前端配置 (src/frontend/.env)

```env
VITE_API_BASE_URL=http://your-server-ip:8080/api
```

---

## 6. 一键启动脚本

### 6.1 开发环境启动 (start-dev.sh)

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

## 7. 常见问题

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

---

## 8. 通过 IP 访问

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
