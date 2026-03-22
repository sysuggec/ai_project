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

### 3.1 部署模式选择

部署脚本支持两种模式：**开发模式** 和 **生产模式**。

#### 开发模式（`--mode dev`）

适用场景：日常开发、快速迭代、代码调试

```bash
# 启动开发环境
./deploy.sh start --mode dev

# 自定义端口
./deploy.sh start --mode dev -p 9000

# 查看日志
./deploy.sh logs --mode dev

# 停止服务
./deploy.sh stop --mode dev
```

**开发模式特点**：
- 源代码挂载到容器 `./src/backend:/var/www/html`
- 修改后端代码后立即生效，无需重启容器
- 修改前端代码后需要在 `src/frontend/` 目录执行 `npm run build`
- 容器名称：`resource-system-dev`

**使用建议**：
- 适合本地开发，代码修改后立即可见
- 前端开发时，可以先单独运行 `npm run dev` 进行热重载
- 后端开发时，修改 PHP 代码后刷新浏览器即可看到效果

#### 生产模式（`--mode prod` 或默认）

适用场景：生产环境、测试环境、稳定部署

```bash
# 启动生产环境（默认模式）
./deploy.sh start

# 显式指定生产模式
./deploy.sh start --mode prod

# 自定义端口
./deploy.sh start -p 9000

# 查看日志
./deploy.sh logs

# 停止服务
./deploy.sh stop
```

**生产模式特点**：
- 使用 `Dockerfile.cached` 多阶段构建
- 代码打包到镜像中，部署更稳定
- 使用预构建的基础镜像加速构建（~30秒）
- 容器名称：`resource-system`

**使用建议**：
- 适合生产环境部署
- 代码更新后需要重新构建镜像
- 多次部署不会重复安装系统依赖

#### 命令对比

| 命令 | 开发模式 | 生产模式 |
|------|---------|---------|
| 启动 | `./deploy.sh start --mode dev` | `./deploy.sh start` |
| 停止 | `./deploy.sh stop --mode dev` | `./deploy.sh stop` |
| 重启 | `./deploy.sh restart --mode dev` | `./deploy.sh restart` |
| 重建 | `./deploy.sh rebuild --mode dev` | `./deploy.sh rebuild` |
| 日志 | `./deploy.sh logs --mode dev` | `./deploy.sh logs` |
| 状态 | `./deploy.sh status --mode dev` | `./deploy.sh status` |

### 3.2 完整命令列表

```bash
# 开发模式命令
./deploy.sh start --mode dev           # 启动开发环境
./deploy.sh stop --mode dev            # 停止开发环境
./deploy.sh restart --mode dev         # 重启开发环境
./deploy.sh rebuild --mode dev         # 重建开发环境
./deploy.sh logs --mode dev            # 查看开发环境日志
./deploy.sh status --mode dev          # 查看开发环境状态

# 生产模式命令（默认）
./deploy.sh start                      # 启动生产环境
./deploy.sh stop                       # 停止生产环境
./deploy.sh restart                    # 重启生产环境
./deploy.sh rebuild                    # 重建生产环境
./deploy.sh logs                       # 查看生产环境日志
./deploy.sh status                     # 查看生产环境状态
./deploy.sh build-base                 # 构建基础镜像
./deploy.sh reset                      # 重置所有数据并重新部署
./deploy.sh clean                      # 清理容器和镜像
./deploy.sh help                       # 显示帮助信息
```

> **注意**：`start` 命令会自动检测基础镜像是否存在，不存在则自动构建。首次部署和后续更新都是一条命令搞定。

### 3.3 构建流程说明

Docker 镜像采用多阶段构建：

1. **前端构建阶段**：Node.js 环境下构建 Vue 应用，输出到 `../backend/public`
2. **后端构建阶段**：PHP-FPM 环境下安装 Composer 依赖
3. **最终镜像**：复制后端代码和前端构建产物

**注意事项**：
- `.dockerignore` 排除了 `src/backend/public/assets/`，避免本地旧构建产物残留
- `index.php` 会被单独复制，确保 PHP 入口文件存在
- 前端构建产物从 `/app/backend/public` 复制，而非 `/app/frontend/dist`

### 3.4 加速构建（基础镜像方案）

为避免每次构建都安装系统依赖，系统采用基础镜像方案。**`start` 命令会自动检测基础镜像**，无需手动构建。

#### 基础镜像内容

基础镜像包含：
- PHP 8.2-FPM
- Nginx
- SQLite
- Composer
- 预创建的目录结构

#### 使用基础镜像构建

修改 `docker-compose.yml` 使用缓存版本：

```yaml
services:
  resource-system:
    build:
      context: .
      dockerfile: Dockerfile.cached  # 使用缓存版本
```

然后正常部署：

```bash
./deploy.sh start -p 8080
```

#### 构建时间对比

| 场景 | 耗时 |
|------|------|
| 首次部署（自动构建基础镜像） | ~5 分钟 |
| 代码更新重建 | **~30 秒** |
| 系统依赖更新（手动 build-base） | ~5 分钟 |

#### 手动构建基础镜像

以下情况需要手动执行 `./deploy.sh build-base`：
- PHP 版本升级
- Nginx/SQLite 等系统依赖版本更新
- 需要安装新的 PHP 扩展
- 基础镜像被意外删除

#### 命令速查

| 命令 | 说明 |
|------|------|
| `./deploy.sh start` | 启动服务（自动检测基础镜像） |
| `./deploy.sh rebuild` | 代码更新后重建（使用基础镜像加速） |
| `./deploy.sh rebuild --no-cache` | 强制完全重建（不重建基础镜像） |
| `./deploy.sh build-base` | 手动构建/更新基础镜像 |

### 3.5 数据持久化注意事项

**重要**：Docker 部署使用卷映射持久化数据，这意味着：

- 重新构建镜像（`deploy.sh rebuild`）不会清除数据库和上传文件
- 旧数据可能包含脏数据，导致目录树显示异常
- 如需全新部署，请使用 `deploy.sh reset` 命令

```bash
# 重置所有数据（清理数据库和上传文件）
./deploy.sh reset
```

### 3.6 环境配置

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

### 3.7 数据卷映射

Docker 部署通过卷映射实现数据持久化：

| 容器路径 | 默认宿主机路径 | 说明 |
|---------|--------------|------|
| `/data/upload` | `./data/upload` | 上传文件存储 |
| `/var/www/html/storage` | `./data/db` | SQLite 数据库 |

### 3.8 Docker Compose 手动操作

#### 开发模式

```bash
# 构建并启动开发环境
docker compose -f docker-compose.dev.yml up -d

# 查看日志
docker compose -f docker-compose.dev.yml logs -f

# 停止服务
docker compose -f docker-compose.dev.yml down

# 重新构建
docker compose -f docker-compose.dev.yml build --no-cache
```

#### 生产模式

```bash
# 构建并启动生产环境
docker compose up -d

# 查看日志
docker compose logs -f

# 停止服务
docker compose down

# 重新构建
docker compose build --no-cache
```

#### 配置文件说明

| 配置文件 | 用途 | 源码挂载 | 基础镜像 |
|---------|------|---------|---------|
| `docker-compose.yml` | 生产环境 | ❌ | ✅ 使用 `Dockerfile.cached` |
| `docker-compose.dev.yml` | 开发环境 | ✅ 挂载 `./src/backend` | ❌ 使用 `Dockerfile` |

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

### Q: 开发模式和生产模式如何选择？
A: 根据使用场景选择：

| 场景 | 推荐模式 | 理由 |
|------|---------|------|
| 日常开发 | `--mode dev` | 代码修改后立即可见，无需重建镜像 |
| 生产部署 | 默认或 `--mode prod` | 使用缓存镜像，部署更稳定 |
| 测试环境 | `--mode prod` | 模拟生产环境，避免开发配置干扰 |
| 快速调试 | `--mode dev` | 无需等待构建，直接修改代码 |

### Q: 开发模式下修改前端代码不生效？
A: 开发模式只挂载后端代码，前端代码需要重新构建：

```bash
# 前端开发时建议使用 Vite 开发服务器
cd src/frontend
npm run dev  # 热重载，修改立即可见

# 或修改后重新构建
npm run build
```

### Q: 如何在生产环境测试新功能？
A: 建议流程：

1. 开发模式完成开发：`./deploy.sh start --mode dev`
2. 生产模式测试：`./deploy.sh start --mode prod`
3. 确认无误后提交代码

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
