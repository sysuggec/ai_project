# v1.1.0 版本实施计划 - 文件管理增强

> **版本代号**: 文件管理增强  
> **计划发布日期**: TBD  
> **文档创建日期**: 2026-03-11  
> **文档版本**: v1.0

---

## 1. 版本概述

### 1.1 版本目标

v1.1.0 版本聚焦于**文件管理增强**，为用户提供更完善的文件操作体验，包括文件移动、回收站、批量操作和文件分享四大核心功能。这些功能将显著提升资源管理系统的易用性和实用性。

### 1.2 功能清单

| 功能模块 | 优先级 | 预计工作量 | 负责模块 |
|---------|--------|-----------|---------|
| 文件/文件夹移动 | 高 | 2-3 小时 | 前端 + 后端 |
| 回收站功能 | 高 | 4-5 小时 | 前端 + 后端 + 数据库 |
| 批量操作 | 高 | 4-5 小时 | 前端 + 后端 |
| 文件分享 | 高 | 5-6 小时 | 前端 + 后端 + 数据库 |

**总计工作量**: 约 15-19 小时

### 1.3 技术栈

- **前端**: Vue 3 + Composition API + Vite
- **后端**: PHP 8.0+ + Eloquent ORM
- **数据库**: SQLite
- **新增依赖**: 无（使用现有技术栈）

---

## 2. 功能详细设计

### 2.1 文件/文件夹移动

#### 2.1.1 功能描述

支持将文件或整个文件夹移动到其他目录，提供拖拽移动和右键菜单两种交互方式。

#### 2.1.2 用户故事

```
作为用户
我想要将文件或文件夹移动到其他目录
以便于重新组织我的文件结构
```

#### 2.1.3 功能要点

**前端交互**：
1. **拖拽移动**：从文件列表拖拽文件/文件夹到左侧目录树目标节点
2. **右键菜单**：右键点击文件/文件夹，选择"移动到"，弹出目录选择对话框
3. **视觉反馈**：拖拽时显示目标目录高亮、移动中 loading 状态、移动成功/失败提示

**后端逻辑**：
1. 验证目标目录是否存在
2. 验证是否有权限移动（如：不能移动到自己的子目录）
3. 更新文件的 `directory_id`（文件移动）
4. 更新文件夹及其所有子文件/子文件夹的 `directory_id` 和 `path`（文件夹移动）
5. 检查目标目录下是否存在同名文件/文件夹，存在则提示冲突

#### 2.1.4 验收标准

- [ ] 可以通过拖拽移动单个文件到目标目录
- [ ] 可以通过拖拽移动整个文件夹到目标目录
- [ ] 可以通过右键菜单移动文件/文件夹
- [ ] 移动后文件在原目录消失，在新目录出现
- [ ] 目标目录存在同名文件时，显示冲突提示
- [ ] 不能将文件夹移动到自己的子目录中
- [ ] 移动操作显示成功/失败提示

#### 2.1.5 边界情况

1. **循环移动**：不能将文件夹移动到自己的子目录
2. **同名冲突**：目标目录存在同名文件/文件夹时的处理策略（提示用户或自动重命名）
3. **移动到根目录**：支持移动到根目录（`parent_id = null`）
4. **并发移动**：同一文件被多次移动时的处理

---

### 2.2 回收站功能

#### 2.2.1 功能描述

删除的文件先进入回收站，支持恢复或彻底删除，并提供自动清理机制。

#### 2.2.2 用户故事

```
作为用户
我想要恢复误删的文件
以便于减少误操作带来的损失
```

#### 2.2.3 功能要点

**数据库设计**：
- 方案 A：新增 `deleted_files` 表（推荐，数据结构清晰）
- 方案 B：在 `files` 表添加 `is_deleted` 和 `deleted_at` 字段（简单，无需新增表）

**前端交互**：
1. 新增"回收站"Tab 页或入口
2. 回收站列表展示：文件名、原位置、删除时间、过期时间
3. 操作按钮：恢复、彻底删除、清空回收站
4. 自动清理倒计时显示

**后端逻辑**：
1. 软删除：删除文件时移动到回收站（设置 `is_deleted = true` 或插入 `deleted_files`）
2. 恢复文件：从回收站恢复到原目录
3. 彻底删除：物理删除文件记录和存储文件
4. 自动清理：定时任务清理超过 N 天的回收站文件（默认 30 天）

#### 2.2.4 验收标准

- [ ] 删除文件后进入回收站，不会立即物理删除
- [ ] 可以在回收站查看已删除的文件列表
- [ ] 可以恢复回收站中的文件到原位置
- [ ] 可以彻底删除回收站中的文件
- [ ] 可以一键清空回收站
- [ ] 回收站文件显示原位置、删除时间、剩余保留时间
- [ ] 自动清理超过 30 天的回收站文件

#### 2.2.5 边界情况

1. **原目录已删除**：恢复时原目录不存在，提示用户选择新目录
2. **原位置同名文件**：恢复时原位置已有同名文件，提示冲突
3. **存储文件丢失**：回收站记录存在但存储文件已被删除的情况
4. **自动清理时机**：后台定时任务或用户访问时触发

---

### 2.3 批量操作

#### 2.3.1 功能描述

支持多选文件后批量删除、批量移动、批量下载。

#### 2.3.2 用户故事

```
作为用户
我想要一次性操作多个文件
以便于提高工作效率
```

#### 2.3.3 功能要点

**前端交互**：
1. 文件列表添加复选框，支持全选/反选
2. 多选后显示操作栏：批量删除、批量移动、批量下载
3. 批量操作进度提示
4. 批量操作结果汇总（成功 X 个，失败 Y 个）

**后端逻辑**：
1. 接收文件 ID 数组
2. 循环处理每个文件的操作
3. 返回汇总结果（成功列表、失败列表及原因）

**批量下载实现**：
- 方案 A：逐个下载（简单，浏览器原生支持多文件下载）
- 方案 B：打包为 ZIP 下载（推荐，需要新增 ZIP 压缩功能，建议 v2.0.0 实现）

#### 2.3.4 验收标准

- [ ] 可以通过复选框多选文件
- [ ] 支持全选/反选功能
- [ ] 多选后显示操作栏
- [ ] 批量删除：显示确认对话框，删除到回收站
- [ ] 批量移动：选择目标目录后移动
- [ ] 批量下载：逐个下载选中的文件
- [ ] 显示批量操作进度和结果汇总

#### 2.3.5 边界情况

1. **部分失败**：批量操作中部分成功部分失败，需要详细的结果反馈
2. **操作中断**：网络中断或用户关闭页面时的处理
3. **权限验证**：批量操作前验证用户对每个文件的权限
4. **性能问题**：批量操作大量文件时的性能优化

---

### 2.4 文件分享

#### 2.4.1 功能描述

生成带过期时间的下载链接，支持密码保护。

#### 2.4.2 用户故事

```
作为用户
我想要生成文件分享链接
以便于与他人共享文件
```

#### 2.4.3 功能要点

**数据库设计**：
- 新增 `shares` 表存储分享记录
- 字段：`token`（唯一标识）、`file_id`、`password`（可选）、`expires_at`、`created_at`

**前端交互**：
1. 文件列表操作栏添加"分享"按钮
2. 分享对话框：设置过期时间、设置密码（可选）
3. 生成分享链接和二维码
4. 复制分享链接、查看分享记录

**后端逻辑**：
1. 生成唯一 token（随机字符串或 UUID）
2. 存储分享记录到数据库
3. 验证分享链接：检查 token 是否存在、是否过期、密码是否正确
4. 返回文件下载流或文件信息

**分享链接格式**：
```
{origin}/share/{token}
```

#### 2.4.4 验收标准

- [ ] 可以为单个文件生成分享链接
- [ ] 可以设置分享链接的过期时间
- [ ] 可以设置访问密码（可选）
- [ ] 生成的分享链接可以直接下载文件
- [ ] 分享链接过期后无法访问
- [ ] 设置密码的链接需要输入密码才能下载
- [ ] 可以查看和管理已创建的分享记录

#### 2.4.5 边界情况

1. **文件被删除**：原文件被删除后分享链接失效
2. **权限控制**：分享链接不登录也可访问
3. **下载次数限制**：可扩展支持下载次数限制
4. **安全性**：token 不可预测，防止暴力破解

---

## 3. 数据库设计

### 3.1 新增表结构

#### 3.1.1 回收站表 (deleted_files)

**方案 A：独立表（推荐）**

```sql
CREATE TABLE deleted_files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_directory_id INTEGER,
    original_directory_path VARCHAR(500),
    file_size INTEGER,
    hash VARCHAR(64),
    storage_path VARCHAR(500),
    deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_deleted_files_expires_at ON deleted_files(expires_at);
CREATE INDEX idx_deleted_files_deleted_at ON deleted_files(deleted_at);
```

**方案 B：扩展现有 files 表**

```sql
ALTER TABLE files ADD COLUMN is_deleted BOOLEAN DEFAULT FALSE;
ALTER TABLE files ADD COLUMN deleted_at DATETIME;
ALTER TABLE files ADD COLUMN original_directory_id INTEGER;

CREATE INDEX idx_files_is_deleted ON files(is_deleted);
```

**推荐方案**：方案 A（独立表），原因：
- 数据结构清晰，易于维护
- 不影响现有文件表查询性能
- 便于实现回收站独立统计和管理

#### 3.1.2 分享表 (shares)

```sql
CREATE TABLE shares (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255),
    expires_at DATETIME,
    download_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
);

CREATE INDEX idx_shares_token ON shares(token);
CREATE INDEX idx_shares_file_id ON shares(file_id);
CREATE INDEX idx_shares_expires_at ON shares(expires_at);
```

### 3.2 数据库迁移计划

```php
// src/backend/database/migrations/2026_03_11_create_deleted_files_table.php
// src/backend/database/migrations/2026_03_11_create_shares_table.php
```

**迁移步骤**：
1. 创建迁移文件
2. 执行数据库迁移脚本
3. 验证表结构和索引

---

## 4. API 接口设计

### 4.1 文件移动接口

#### 移动文件

```
PUT /api/files/{id}/move
```

**请求参数**：
```json
{
  "target_directory_id": 5
}
```

**响应**：
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "example.txt",
    "directory_id": 5
  }
}
```

**错误响应**：
```json
{
  "success": false,
  "error": "目标目录不存在"
}
```

```json
{
  "success": false,
  "error": "目标目录已存在同名文件"
}
```

```json
{
  "success": false,
  "error": "不能将文件夹移动到自己的子目录中"
}
```

### 4.2 回收站接口

#### 获取回收站列表

```
GET /api/trash
```

**响应**：
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "file_name": "example.txt",
      "original_directory_path": "/documents",
      "file_size": 1024,
      "deleted_at": "2026-03-10 10:30:00",
      "expires_at": "2026-04-09 10:30:00"
    }
  ]
}
```

#### 恢复文件

```
POST /api/trash/{id}/restore
```

**响应**：
```json
{
  "success": true,
  "message": "文件已恢复到原位置"
}
```

#### 彻底删除

```
DELETE /api/trash/{id}
```

**响应**：
```json
{
  "success": true,
  "message": "文件已彻底删除"
}
```

#### 清空回收站

```
DELETE /api/trash
```

**响应**：
```json
{
  "success": true,
  "message": "回收站已清空"
}
```

### 4.3 批量操作接口

#### 批量删除

```
POST /api/files/batch-delete
```

**请求参数**：
```json
{
  "file_ids": [1, 2, 3, 4, 5]
}
```

**响应**：
```json
{
  "success": true,
  "data": {
    "success_count": 4,
    "failed_count": 1,
    "failed_items": [
      {
        "file_id": 3,
        "reason": "文件不存在"
      }
    ]
  }
}
```

#### 批量移动

```
POST /api/files/batch-move
```

**请求参数**：
```json
{
  "file_ids": [1, 2, 3],
  "target_directory_id": 5
}
```

**响应**：
```json
{
  "success": true,
  "data": {
    "success_count": 3,
    "failed_count": 0,
    "failed_items": []
  }
}
```

### 4.4 文件分享接口

#### 创建分享链接

```
POST /api/shares
```

**请求参数**：
```json
{
  "file_id": 123,
  "expires_in": 86400,
  "password": "optional_password"
}
```

**响应**：
```json
{
  "success": true,
  "data": {
    "token": "abc123def456",
    "share_url": "http://example.com/share/abc123def456",
    "expires_at": "2026-03-12 10:30:00",
    "has_password": true
  }
}
```

#### 获取分享文件信息

```
GET /api/shares/{token}
```

**请求参数**（如有密码）：
```json
{
  "password": "user_input_password"
}
```

**响应**：
```json
{
  "success": true,
  "data": {
    "file_name": "example.txt",
    "file_size": 1024,
    "download_url": "/api/shares/abc123def456/download"
  }
}
```

**错误响应**：
```json
{
  "success": false,
  "error": "分享链接已过期"
}
```

```json
{
  "success": false,
  "error": "密码错误"
}
```

#### 下载分享文件

```
GET /api/shares/{token}/download
```

**响应**：文件流

#### 获取分享列表

```
GET /api/shares
```

**响应**：
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "file_id": 123,
      "file_name": "example.txt",
      "token": "abc123def456",
      "expires_at": "2026-03-12 10:30:00",
      "download_count": 10,
      "created_at": "2026-03-11 10:30:00"
    }
  ]
}
```

#### 删除分享

```
DELETE /api/shares/{id}
```

**响应**：
```json
{
  "success": true,
  "message": "分享已删除"
}
```

---

## 5. 前端设计

### 5.1 新增组件

```
src/frontend/src/components/
├── resource/
│   ├── FileList.vue (修改)
│   ├── DirectoryTree.vue (修改)
│   ├── BatchOperationBar.vue (新增)
│   ├── MoveDialog.vue (新增)
│   └── ShareDialog.vue (新增)
├── trash/
│   ├── TrashList.vue (新增)
│   └── TrashItem.vue (新增)
└── share/
    ├── ShareAccess.vue (新增)
    └── ShareManager.vue (新增)
```

### 5.2 组件设计详情

#### 5.2.1 BatchOperationBar.vue

**功能**：批量操作工具栏

**Props**：
- `selectedFiles`: 选中的文件列表
- `visible`: 是否显示

**Events**：
- `batch-delete`: 批量删除
- `batch-move`: 批量移动
- `batch-download`: 批量下载
- `clear-selection`: 清除选择

**UI 设计**：
```
┌─────────────────────────────────────────────────────┐
│ 已选择 5 个文件  [批量删除] [批量移动] [批量下载] [取消] │
└─────────────────────────────────────────────────────┘
```

#### 5.2.2 MoveDialog.vue

**功能**：移动文件/文件夹对话框

**Props**：
- `visible`: 是否显示
- `file`: 要移动的文件信息
- `directories`: 目录树数据

**Events**：
- `confirm`: 确认移动
- `cancel`: 取消

**UI 设计**：
```
┌──────────────────────────────────┐
│        移动到目录                 │
├──────────────────────────────────┤
│ 📁 根目录                         │
│   📁 documents                   │
│   📁 images                      │
│   📁 downloads (当前)             │
├──────────────────────────────────┤
│              [取消] [确定]        │
└──────────────────────────────────┘
```

#### 5.2.3 ShareDialog.vue

**功能**：创建分享链接对话框

**Props**：
- `visible`: 是否显示
- `file`: 要分享的文件信息

**Events**：
- `confirm`: 确认创建
- `cancel`: 取消

**UI 设计**：
```
┌──────────────────────────────────┐
│           创建分享链接            │
├──────────────────────────────────┤
│ 文件名: example.txt              │
│ 过期时间: [1天 ▼] [自定义]        │
│ 访问密码: [     ] (可选)          │
├──────────────────────────────────┤
│ 分享链接: http://xxx/share/abc   │
│          [复制链接] [生成二维码]   │
├──────────────────────────────────┤
│              [取消] [创建]        │
└──────────────────────────────────┘
```

#### 5.2.4 TrashList.vue

**功能**：回收站文件列表

**Events**：
- `restore`: 恢复文件
- `delete-permanently`: 彻底删除
- `clear-all`: 清空回收站

**UI 设计**：
```
┌──────────────────────────────────────────────────────┐
│ 回收站 (10 个文件)                    [清空回收站]    │
├──────────────────────────────────────────────────────┤
│ ☐ 文件名    原位置      大小   删除时间    剩余时间   操作│
│ ☐ file.txt /documents  1KB   3天前      27天       [恢复] [删除]│
│ ☐ image.png /images    2MB   5天前      25天       [恢复] [删除]│
└──────────────────────────────────────────────────────┘
```

#### 5.2.5 ShareAccess.vue

**功能**：分享链接访问页面（独立页面）

**路由**：`/share/:token`

**UI 设计**：
```
┌──────────────────────────────────┐
│         文件分享                 │
├──────────────────────────────────┤
│ 文件名: example.txt              │
│ 大小: 1 KB                       │
│                                  │
│ 访问密码: [          ]           │
│                                  │
│         [下载文件]               │
└──────────────────────────────────┘
```

### 5.3 API 封装

```javascript
// src/frontend/src/api/file.js (新增)
export const moveFile = (fileId, targetDirectoryId) => {
  return request.put(`/api/files/${fileId}/move`, { target_directory_id: targetDirectoryId })
}

export const batchDeleteFiles = (fileIds) => {
  return request.post('/api/files/batch-delete', { file_ids: fileIds })
}

export const batchMoveFiles = (fileIds, targetDirectoryId) => {
  return request.post('/api/files/batch-move', { file_ids: fileIds, target_directory_id: targetDirectoryId })
}
```

```javascript
// src/frontend/src/api/trash.js (新增)
export const getTrashList = () => {
  return request.get('/api/trash')
}

export const restoreFile = (id) => {
  return request.post(`/api/trash/${id}/restore`)
}

export const deletePermanently = (id) => {
  return request.delete(`/api/trash/${id}`)
}

export const clearTrash = () => {
  return request.delete('/api/trash')
}
```

```javascript
// src/frontend/src/api/share.js (新增)
export const createShare = (fileId, expiresIn, password) => {
  return request.post('/api/shares', { file_id: fileId, expires_in: expiresIn, password })
}

export const getShareInfo = (token, password) => {
  return request.get(`/api/shares/${token}`, { params: { password } })
}

export const getShareList = () => {
  return request.get('/api/shares')
}

export const deleteShare = (id) => {
  return request.delete(`/api/shares/${id}`)
}
```

### 5.4 Composables 设计

```javascript
// src/frontend/src/composables/useBatchOperation.js (新增)
export function useBatchOperation() {
  const selectedFiles = ref([])
  
  const selectFile = (file) => { /* ... */ }
  const deselectFile = (fileId) => { /* ... */ }
  const selectAll = (files) => { /* ... */ }
  const clearSelection = () => { /* ... */ }
  
  return {
    selectedFiles,
    selectFile,
    deselectFile,
    selectAll,
    clearSelection
  }
}
```

```javascript
// src/frontend/src/composables/useTrash.js (新增)
export function useTrash() {
  const trashList = ref([])
  const loading = ref(false)
  
  const fetchTrashList = async () => { /* ... */ }
  const restoreFile = async (id) => { /* ... */ }
  const deletePermanently = async (id) => { /* ... */ }
  
  return {
    trashList,
    loading,
    fetchTrashList,
    restoreFile,
    deletePermanently
  }
}
```

```javascript
// src/frontend/src/composables/useShare.js (新增)
export function useShare() {
  const shareList = ref([])
  const currentShare = ref(null)
  
  const createShare = async (fileId, options) => { /* ... */ }
  const fetchShareList = async () => { /* ... */ }
  const deleteShare = async (id) => { /* ... */ }
  
  return {
    shareList,
    currentShare,
    createShare,
    fetchShareList,
    deleteShare
  }
}
```

---

## 6. 后端设计

### 6.1 新增文件结构

```
src/backend/app/
├── Controllers/
│   ├── FileController.php (修改)
│   ├── TrashController.php (新增)
│   └── ShareController.php (新增)
├── Models/
│   ├── File.php (修改)
│   ├── DeletedFile.php (新增)
│   └── Share.php (新增)
├── Services/
│   ├── FileService.php (修改)
│   ├── TrashService.php (新增)
│   └── ShareService.php (新增)
```

### 6.2 路由扩展

```php
// src/backend/routes/api.php

// 文件移动
$router->put('/files/{id}/move', [FileController::class, 'move']);

// 批量操作
$router->post('/files/batch-delete', [FileController::class, 'batchDelete']);
$router->post('/files/batch-move', [FileController::class, 'batchMove']);

// 回收站
$router->get('/trash', [TrashController::class, 'index']);
$router->post('/trash/{id}/restore', [TrashController::class, 'restore']);
$router->delete('/trash/{id}', [TrashController::class, 'deletePermanently']);
$router->delete('/trash', [TrashController::class, 'clear']);

// 文件分享
$router->post('/shares', [ShareController::class, 'create']);
$router->get('/shares', [ShareController::class, 'index']);
$router->get('/shares/{token}', [ShareController::class, 'show']);
$router->get('/shares/{token}/download', [ShareController::class, 'download']);
$router->delete('/shares/{id}', [ShareController::class, 'delete']);
```

### 6.3 Service 层设计

#### 6.3.1 FileService 扩展

```php
// 新增方法
public function move(int $fileId, int $targetDirectoryId): array;
public function batchDelete(array $fileIds): array;
public function batchMove(array $fileIds, int $targetDirectoryId): array;
```

#### 6.3.2 TrashService

```php
class TrashService
{
    public function getList(): array;
    public function restore(int $id): bool;
    public function deletePermanently(int $id): bool;
    public function clear(): int;
    public function autoCleanup(): int;
}
```

#### 6.3.3 ShareService

```php
class ShareService
{
    public function create(int $fileId, int $expiresIn, ?string $password): array;
    public function getByToken(string $token, ?string $password): array;
    public function getList(): array;
    public function delete(int $id): bool;
    public function generateToken(): string;
    public function isExpired(Share $share): bool;
}
```

---

## 7. 实施步骤

### 7.1 阶段划分

#### 阶段一：数据库准备（1 小时）

1. **创建数据库迁移文件**
   - `deleted_files` 表
   - `shares` 表

2. **创建 Model 类**
   - `DeletedFile.php`
   - `Share.php`

3. **执行数据库迁移**
   - 验证表结构
   - 验证索引

#### 阶段二：文件移动功能（2-3 小时）

1. **后端开发**（1.5 小时）
   - FileService 添加 `move()` 方法
   - FileController 添加 `move()` 方法
   - 添加路由
   - 测试 API

2. **前端开发**（1 小时）
   - DirectoryTree 组件添加拖拽接收
   - FileList 组件添加拖拽功能
   - 创建 MoveDialog 组件
   - 添加右键菜单
   - API 封装
   - 集成测试

#### 阶段三：回收站功能（4-5 小时）

1. **后端开发**（2.5 小时）
   - 创建 TrashService
   - 创建 TrashController
   - 修改文件删除逻辑（软删除）
   - 实现自动清理机制
   - 添加路由
   - 测试 API

2. **前端开发**（2 小时）
   - 创建 TrashList 组件
   - 创建 TrashItem 组件
   - 添加回收站入口（Tab 或按钮）
   - API 封装
   - 集成测试

#### 阶段四：批量操作功能（4-5 小时）

1. **后端开发**（2 小时）
   - FileService 添加 `batchDelete()` 方法
   - FileService 添加 `batchMove()` 方法
   - FileController 添加批量操作方法
   - 添加路由
   - 测试 API

2. **前端开发**（2.5 小时）
   - FileList 组件添加复选框
   - 创建 BatchOperationBar 组件
   - 实现全选/反选逻辑
   - 集成移动对话框复用
   - 批量下载逻辑
   - 集成测试

#### 阶段五：文件分享功能（5-6 小时）

1. **后端开发**（3 小时）
   - 创建 ShareService
   - 创建 ShareController
   - token 生成逻辑
   - 密码加密存储
   - 过期验证逻辑
   - 添加路由
   - 测试 API

2. **前端开发**（2.5 小时）
   - 创建 ShareDialog 组件
   - 创建 ShareAccess 页面
   - 创建 ShareManager 组件
   - 文件列表添加分享按钮
   - 路由配置
   - API 封装
   - 集成测试

#### 阶段六：集成测试与优化（2 小时）

1. E2E 测试编写
2. 边界情况处理
3. 性能优化
4. 代码审查
5. 文档更新

### 7.2 实施顺序（按优先级）

```
1. 数据库准备
   └── 创建表结构、Model

2. 文件移动功能
   └── 后端 API → 前端交互

3. 回收站功能
   └── 修改删除逻辑 → 后端 API → 前端页面

4. 批量操作功能
   └── 后端 API → 前端多选和操作栏

5. 文件分享功能
   └── 后端 API → 前端分享对话框和访问页面

6. 集成测试与优化
   └── E2E 测试、性能优化、文档更新
```

### 7.3 时间估算

| 阶段 | 后端 | 前端 | 测试 | 总计 |
|-----|------|------|------|------|
| 数据库准备 | 1h | - | - | 1h |
| 文件移动 | 1.5h | 1h | 0.5h | 3h |
| 回收站 | 2.5h | 2h | 0.5h | 5h |
| 批量操作 | 2h | 2.5h | 0.5h | 5h |
| 文件分享 | 3h | 2.5h | 0.5h | 6h |
| 集成测试 | - | - | 2h | 2h |
| **总计** | **10h** | **8h** | **4h** | **22h** |

> 注：实际工作量可能与预估有差异，建议预留 20% 缓冲时间。

---

## 8. 测试计划

### 8.1 单元测试

#### 后端单元测试

- FileService::move() - 文件移动逻辑
- FileService::batchDelete() - 批量删除逻辑
- FileService::batchMove() - 批量移动逻辑
- TrashService::restore() - 文件恢复逻辑
- TrashService::autoCleanup() - 自动清理逻辑
- ShareService::create() - 分享创建逻辑
- ShareService::isExpired() - 过期判断逻辑

#### 前端单元测试

- useBatchOperation 组合式函数
- useTrash 组合式函数
- useShare 组合式函数

### 8.2 集成测试

- 文件移动 API 测试
- 回收站完整流程测试
- 批量操作 API 测试
- 文件分享完整流程测试

### 8.3 E2E 测试

#### 测试场景

1. **文件移动**
   - 拖拽文件到目标目录
   - 右键菜单移动文件
   - 移动文件夹
   - 同名文件冲突处理
   - 循环移动检测

2. **回收站**
   - 删除文件到回收站
   - 恢复文件
   - 彻底删除文件
   - 清空回收站
   - 自动清理验证

3. **批量操作**
   - 多选文件
   - 批量删除
   - 批量移动
   - 批量下载
   - 部分失败处理

4. **文件分享**
   - 创建分享链接
   - 访问分享链接（无密码）
   - 访问分享链接（有密码）
   - 分享链接过期
   - 管理分享记录

### 8.4 测试文件结构

```
playwright/tests/
├── file-move.spec.js (新增)
├── trash.spec.js (新增)
├── batch-operation.spec.js (新增)
└── share.spec.js (新增)
```

---

## 9. 风险评估

### 9.1 技术风险

| 风险项 | 风险等级 | 影响描述 | 应对措施 |
|-------|---------|---------|---------|
| 大量文件批量操作性能 | 中 | 批量操作大量文件时响应慢 | 分页处理、异步队列 |
| 回收站自动清理时机 | 低 | 定时任务需要后台进程 | 用户访问时触发或使用 cron |
| 分享链接安全性 | 中 | token 被猜测或泄露 | 使用加密随机 token、HTTPS |
| 文件夹移动复杂度 | 中 | 递归移动可能出错 | 充分测试、事务保护 |

### 9.2 业务风险

| 风险项 | 风险等级 | 影响描述 | 应对措施 |
|-------|---------|---------|---------|
| 用户误操作 | 低 | 误移动或误删除文件 | 确认对话框、回收站保护 |
| 分享链接滥用 | 低 | 大量文件被分享 | 分享数量限制、下载次数限制 |
| 回收站占用空间 | 低 | 大量文件留在回收站 | 自动清理、空间提醒 |

### 9.3 兼容性风险

| 风险项 | 风险等级 | 影响描述 | 应对措施 |
|-------|---------|---------|---------|
| 拖拽功能浏览器支持 | 低 | 部分浏览器不支持拖拽 | 提供右键菜单替代方案 |
| localStorage 存储限制 | 低 | 分享信息存储限制 | 主要存储在服务端 |

---

## 10. 后续优化建议

### 10.1 v1.1.1 潜在优化

- 批量操作进度条优化
- 回收站自动清理配置化
- 分享链接下载次数统计
- 移动操作撤销功能

### 10.2 v1.2.0 功能延伸

- 批量下载打包为 ZIP（需要 PHP ZipArchive）
- 分享链接支持文件夹
- 回收站文件预览
- 移动操作历史记录

---

## 11. 参考文档

- [项目技术方案](./solution.md)
- [部署指南](./deployment.md)
- [PHP 代码规范](../.codebuddy/rules/coding-style)
- [版本路线图](./roadmap.md)

---

## 12. 变更记录

| 日期 | 版本 | 变更内容 | 作者 |
|------|------|---------|------|
| 2026-03-11 | v1.0 | 初始创建实施计划文档 | - |

---

## 附录 A：数据库 ER 图

```
┌─────────────┐
│ directories │
├─────────────┤
│ id          │───┐
│ name        │   │
│ path        │   │
│ parent_id   │◄──┘
└─────────────┘
       │
       │ 1:N
       │
┌─────────────┐        ┌──────────────┐
│   files     │        │ deleted_files│
├─────────────┤        ├──────────────┤
│ id          │──┐     │ id           │
│ name        │  │     │ file_id      │◄──┐
│ hash        │  │     │ file_name    │   │
│ size        │  │     │ original_dir │   │
│ directory_id│◄─┼──┐   │ deleted_at   │   │
│ storage_path│  │  │   │ expires_at   │   │
│ is_deleted  │  │  │   └──────────────┘   │
└─────────────┘  │  │                      │
                 │  │   ┌──────────────┐   │
                 │  │   │    shares    │   │
                 │  │   ├──────────────┤   │
                 │  │   │ id           │   │
                 │  │   │ file_id      │◄──┘
                 │  │   │ token        │
                 │  │   │ password     │
                 │  │   │ expires_at   │
                 │  │   └──────────────┘
                 │  │
                 │  └─── 1:N (一个目录多个文件)
                 │
                 └───── 1:N (一个文件多次分享)
```

---

## 附录 B：前端路由规划

```javascript
// src/frontend/src/router/index.js

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', redirect: '/resources' },
      { path: 'resources', component: ResourceList },
      { path: 'upload', component: UploadArea },
      { path: 'trash', component: TrashList }, // 新增
      { path: 'shares', component: ShareManager } // 新增
    ]
  },
  { path: '/share/:token', component: ShareAccess } // 新增，独立页面
]
```

---

## 附录 C：UI/UX 设计要点

### C.1 视觉反馈

- **拖拽状态**：目标目录高亮、拖拽文件半透明
- **Loading 状态**：操作进行中显示 loading 动画
- **成功/失败提示**：使用 Toast 组件显示操作结果

### C.2 确认对话框

所有破坏性操作需要确认：
- 删除文件 → 移到回收站（确认）
- 彻底删除 → 不可恢复（二次确认）
- 清空回收站 → 全部删除（二次确认）
- 批量操作 → 显示数量确认

### C.3 快捷操作

- **拖拽**：拖拽文件移动
- **右键菜单**：常用操作快捷入口
- **键盘快捷键**：Delete 删除、Ctrl+A 全选

---

**文档结束**
