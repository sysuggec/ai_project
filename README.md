# 资源上传下载管理系统

一个轻量级的文件资源管理系统，支持秒传、文件夹上传、目录管理等功能。

## 功能特性

- **文件上传**：支持拖拽上传、多文件上传、文件夹上传
- **秒传功能**：基于 SHA-256 哈希去重，相同文件无需重复上传
- **资源管理**：按目录结构管理文件，支持搜索
- **文件操作**：下载、复制下载链接、重命名、删除、查看物理位置
- **上传历史**：查看所有上传记录

## 技术栈

- **前端**：Vue 3 + Composition API + Vite
- **后端**：PHP 8.2 + Eloquent ORM + SQLite
- **部署**：Docker 多阶段构建

## 快速开始

### Docker 一键部署

```bash
# 一键启动（自动检测并构建基础镜像）
./deploy.sh start

# 自定义端口
./deploy.sh start -p 9000

# 代码更新后重建
./deploy.sh rebuild

# 其他命令
./deploy.sh stop       # 停止服务
./deploy.sh logs       # 查看日志
```

### 本地开发

```bash
# 后端
cd src/backend && composer install
php -S 0.0.0.0:8080 -t public

# 前端
cd src/frontend && npm install
npm run dev
```

## 文档

- [技术方案](docs/solution.md) - 架构设计、API 设计、数据库设计
- [部署指南](docs/deployment.md) - 环境要求、Docker 部署、生产配置

## 项目结构

```
├── src/
│   ├── frontend/     # Vue 3 前端
│   └── backend/      # PHP 后端
├── docker/           # Docker 配置
├── docs/             # 文档
└── deploy.sh         # 一键部署脚本
```

## 许可证

MIT
