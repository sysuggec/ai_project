# 资源上传下载系统 - 技术方案

## 1. 项目概述

### 1.1 功能需求
- **上传功能**：支持文件/文件夹拖拽上传、多文件同时上传、显示上传进度和结果、可选择保存子目录
- **资源管理**：按目录结构展示资源列表、点击下载资源、复制下载链接
- **图片预览**：图片文件支持在线预览，点击预览按钮打开全屏图片查看器
- **高级功能**：秒传（SHA-256 哈希去重）、上传历史记录、搜索功能
- **文件操作**：删除、重命名（保持原文件名）、查看文件物理位置

### 1.2 技术选型
- **前端**：Vue 3 + Composition API + 响应式设计
- **后端**：原生 PHP 8.0+ + Eloquent ORM + Composer 自动加载
- **数据库**：SQLite（轻量级）
- **存储根目录**：`/data/upload`

---

## 2. 数据库设计

### 2.1 目录表 (directories)
```sql
CREATE TABLE directories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    parent_id INTEGER DEFAULT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE(path)
);
```

### 2.2 文件表 (files)
```sql
CREATE TABLE files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    hash VARCHAR(64) NOT NULL,
    size INTEGER NOT NULL,
    mime_type VARCHAR(100),
    directory_id INTEGER NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    upload_time DATETIME,
    created_at DATETIME,
    updated_at DATETIME
);
```

### 2.3 上传历史表 (upload_histories)
```sql
CREATE TABLE upload_histories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER,
    original_name VARCHAR(255) NOT NULL,
    target_directory VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    hash VARCHAR(64),
    is_instant_upload BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) NOT NULL,
    error_message TEXT,
    upload_time DATETIME
);
```

---

## 3. API 接口设计

| 方法 | 路径 | 功能 |
|------|------|------|
| POST | `/api/upload/check` | 秒传检查（校验文件哈希） |
| POST | `/api/upload/file` | 上传文件 |
| POST | `/api/upload/folder` | 上传文件夹 |
| GET | `/api/upload/history` | 获取上传历史 |
| GET | `/api/files` | 文件列表（支持搜索，返回 `storage_path` 物理路径） |
| GET | `/api/directories` | 获取目录树 |
| POST | `/api/directories` | 创建目录 |
| GET | `/api/files/{id}/download` | 下载文件 |
| DELETE | `/api/files/{id}` | 删除文件 |
| PUT | `/api/files/{id}` | 重命名文件 |

> **复制下载链接**：前端生成下载链接 `{origin}/api/files/{id}/download` 并复制到剪贴板，无需后端接口。
>
> **查看物理位置**：文件列表 API 返回 `storage_path` 字段，前端点击按钮复制到剪贴板。

---

## 4. 秒传实现方案

### 4.1 原理
- 前端计算文件 SHA-256 哈希值
- 上传前先调用 `/api/upload/check` 接口检查哈希是否存在
- 若存在，直接创建文件记录，无需上传实体文件
- 若不存在，正常上传文件

### 4.2 安全上下文要求

秒传功能依赖 **Web Crypto API** (`crypto.subtle.digest`)，该 API 只在安全上下文中可用：

| 访问方式 | 秒传功能 | 上传功能 |
|---------|---------|---------|
| HTTPS | ✅ 支持 | ✅ 正常 |
| localhost / 127.0.0.1 | ✅ 支持 | ✅ 正常 |
| HTTP（非本地） | ❌ 不可用 | ✅ 正常（后端计算哈希） |

**降级策略**：
- 在非安全上下文中，前端跳过哈希计算和秒传检查
- 后端在上传时自动计算文件哈希，确保功能正常
- 用户可正常上传文件，只是无法享受秒传加速

### 4.3 存储策略
```
/data/upload/
├── ab/
│   └── abc123...def/  (以哈希前2位分层)
├── cd/
│   └── cde456...abc/
```

---

## 5. 前端组件结构

```
src/frontend/
├── App.vue              # 主应用（Tab 切换）
├── components/
│   ├── upload/
│   │   ├── UploadArea.vue      # 上传区域（拖拽）
│   │   ├── FileSelector.vue    # 文件选择器
│   │   ├── DirectoryPicker.vue # 目录选择器
│   │   ├── UploadQueue.vue     # 上传队列
│   │   └── UploadProgress.vue  # 上传进度
│   ├── resource/
│   │   ├── DirectoryTree.vue   # 目录树
│   │   ├── FileList.vue        # 文件列表
│   │   ├── FileCard.vue        # 文件卡片
│   │   └── SearchBar.vue       # 搜索栏
│   └── common/
│       ├── ProgressBar.vue     # 进度条
│       ├── Toast.vue           # 提示消息
│       ├── Modal.vue           # 模态框
│       ├── ConfirmDialog.vue   # 确认对话框
│       ├── RenameDialog.vue    # 重命名对话框
│       └── ImagePreview.vue    # 图片预览组件
├── api/
│   ├── upload.js        # 上传相关 API
│   └── resource.js      # 资源管理 API
├── utils/
│   ├── hash.js          # 哈希计算工具
│   └── file.js          # 文件处理工具
└── composables/
    ├── useUpload.js     # 上传逻辑复用
    └── useResource.js   # 资源管理逻辑复用
```

---

## 6. 后端目录结构

```
src/backend/
├── public/
│   └── index.php        # 入口文件
├── app/
│   ├── Controllers/
│   │   ├── UploadController.php
│   │   ├── FileController.php
│   │   └── DirectoryController.php
│   ├── Models/
│   │   ├── File.php
│   │   ├── Directory.php
│   │   └── UploadHistory.php
│   ├── Services/
│   │   ├── UploadService.php
│   │   ├── FileService.php
│   │   └── HashService.php
│   └── Middleware/
│       └── CorsMiddleware.php
├── config/
│   └── database.php     # 数据库配置
├── routes/
│   └── api.php          # 路由定义
├── storage/
│   └── database.sqlite  # SQLite 数据库
└── vendor/              # Composer 依赖
```

---

## 7. 文件夹上传实现

使用 HTML5 `webkitdirectory` API：
```html
<input type="file" webkitdirectory directory multiple>
```

前端递归读取文件夹结构，保持相对路径，后端根据路径创建对应目录。

---

## 8. 移动端适配

- 响应式布局（CSS Grid + Flexbox）
- 触摸友好：按钮大小 ≥ 44px
- 移动端优先设计
- 支持触摸拖拽上传
