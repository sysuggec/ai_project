# CODEBUDDY.md This file provides guidance to CodeBuddy when working with code in this repository.

## 项目简介

资源上传下载管理系统，前后端分离架构，支持秒传（SHA-256 哈希去重）、文件/文件夹拖拽上传。

## 技术栈

- **前端**: Vue 3 + Composition API + Vite
- **后端**: 原生 PHP 8.0+ + Eloquent ORM（自定义轻量级框架，非 Laravel）
- **数据库**: SQLite
- **HTTP**: Symfony HttpFoundation
- **E2E 测试**: Playwright

## 核心命令

```bash
# 后端启动（生产模式）
cd src/backend && php -S 0.0.0.0:8080 -t public

# 前端开发（开发模式，自动代理到 :8080）
cd src/frontend && npm run dev

# 前端构建（输出到 src/backend/public/）
cd src/frontend && npm run build

# 安装依赖
cd src/backend && composer install
cd src/frontend && npm install

# Docker 一键部署
./deploy.sh start                    # 一键启动（自动检测基础镜像）
./deploy.sh start -p 9000            # 自定义端口
./deploy.sh rebuild                  # 代码更新后重建（~30秒）
./deploy.sh build-base               # 手动更新基础镜像
./deploy.sh stop                     # 停止服务
./deploy.sh logs                     # 查看日志

# E2E 测试
cd playwright && npm install           # 首次安装测试依赖
cd playwright && npm test              # 运行所有测试
cd playwright && npm run test:ui       # UI 模式运行测试
cd playwright && npm run report        # 查看测试报告
```

## 架构要点

**后端核心**：
- 单一入口 `public/index.php`，路由分发 + 静态资源服务
- 自定义路由系统（`Application` 类），支持路径参数 `{id}`
- Controller → Service → Model 三层架构
- 中间件模式处理 CORS
- 无 Laravel 容器，手动依赖注入

**前端核心**：
- Composition API 复用逻辑（`useUpload`, `useResource`）
- API 层封装（`src/api/`）
- Vite 开发代理避免跨域
- 构建集成到后端 `public/` 目录

**秒传机制**：
- 前端计算 SHA-256 → `/api/upload/check` 检查哈希
- 存在则直接创建文件记录，不存在则上传文件
- 存储路径：`/data/upload/{hash前2位}/{hash}`

**数据库**：
- `directories`: 目录树
- `files`: 文件记录（含哈希、存储路径）
- `upload_histories`: 上传历史

## 详细文档索引

- **技术方案**: `docs/solution.md` - 架构设计、API 设计、数据库设计、组件结构
- **部署指南**: `docs/deployment.md` - 环境要求、Nginx 配置、生产部署、常见问题
- **测试文档**: `test/README.md` - 测试报告、测试脚本说明
- **E2E 测试**: `playwright/README.md` - Playwright 端到端测试说明
- **PHP 代码规范**: 参考 `.codebuddy/rules/coding-style` 中的 PHP 规范

## 开发约定

- PHP: `declare(strict_types=1)` + PSR-1/PSR-2/PSR-4 + 类型声明
- API: RESTful，统一响应 `{success, data?, error?}`
- 前端: 组件 PascalCase，组合式函数 `use*`
