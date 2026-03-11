# 版本路线图

> 本文档记录资源上传下载系统的功能规划与版本计划

---

## 版本历史

| 版本 | 发布日期 | 主要功能 |
|------|----------|----------|
| v1.0.0 | - | 基础上传下载、秒传、预览功能 |

---

## v1.1.0 - 文件管理增强 (计划中)

### 新增功能

#### 1. 文件/文件夹移动
- **优先级**: 高
- **描述**: 支持将文件或整个文件夹移动到其他目录
- **前端**: 拖拽移动 + 右键菜单
- **后端**: 新增 `PUT /api/files/{id}/move` 接口
- **预计工作量**: 小（约 2-3 小时）

#### 2. 回收站功能
- **优先级**: 高
- **描述**: 删除的文件先进入回收站，支持恢复或彻底删除
- **数据库**: 新增 `deleted_files` 表或添加 `is_deleted` 字段
- **功能**: 自动清理超过 N 天的回收站文件
- **预计工作量**: 中（约 4-5 小时）

#### 3. 批量操作
- **优先级**: 高
- **描述**: 多选文件后批量删除、批量移动、批量下载
- **前端**: checkbox 多选 + 操作栏
- **预计工作量**: 中（约 4-5 小时）

#### 4. 文件分享（临时链接）
- **优先级**: 高
- **描述**: 生成带过期时间的下载链接，支持密码保护
- **数据库**: 新增 `shares` 表存储分享记录
- **预计工作量**: 中（约 5-6 小时）

---

## v1.2.0 - 存储与预览增强 (计划中)

### 新增功能

#### 5. 存储空间统计
- **优先级**: 中
- **描述**: 仪表盘展示总存储、已用空间、文件数量统计
- **UI**: 按文件类型分布图表
- **预计工作量**: 小（约 2 小时）

#### 6. 大文件分片上传
- **优先级**: 中
- **描述**: 支持超大文件断点续传
- **实现**: 前端分片上传 + 后端合并
- **预计工作量**: 大（约 8-10 小时）

#### 7. 音频播放器
- **优先级**: 中
- **描述**: 新增 `AudioPlayer.vue` 组件
- **支持格式**: mp3, wav, flac, aac 等
- **预计工作量**: 小（约 2-3 小时）

#### 8. PDF 在线预览
- **优先级**: 中
- **描述**: 使用 PDF.js 实现 PDF 预览
- **功能**: 支持缩放、翻页、搜索
- **预计工作量**: 中（约 4-5 小时）

#### 9. Office 文档预览
- **优先级**: 中
- **描述**: 支持 Word、Excel、PowerPoint 在线预览
- **方案**: 可集成第三方服务或使用前端库
- **预计工作量**: 中（约 5-6 小时）

---

## v2.0.0 - 体验优化与协作 (远期规划)

### 新增功能

#### 10. 用户系统与权限
- **优先级**: 低
- **描述**: 登录/注册功能，私有空间与公共空间，文件权限管理
- **预计工作量**: 大（约 15-20 小时）

#### 11. 高级搜索
- **优先级**: 低
- **描述**: 
  - 按文件类型筛选
  - 按大小范围筛选
  - 按上传日期范围筛选
  - 全文搜索（针对文本文件）
- **预计工作量**: 中（约 5-6 小时）

#### 12. 文件标签与收藏
- **优先级**: 低
- **描述**: 自定义标签分类、收藏夹功能、文件备注
- **数据库**: 新增 `tags`、`favorites` 表
- **预计工作量**: 中（约 4-5 小时）

#### 13. 主题切换
- **优先级**: 低
- **描述**: 暗色/亮色模式切换，个性化设置持久化
- **预计工作量**: 小（约 2-3 小时）

#### 14. 国际化 (i18n)
- **优先级**: 低
- **描述**: 支持中英文切换，使用 vue-i18n
- **预计工作量**: 中（约 6-8 小时）

#### 15. 文件压缩下载
- **优先级**: 低
- **描述**: 多文件/文件夹打包为 ZIP 下载
- **后端**: 使用 PHP ZipArchive 类
- **预计工作量**: 小（约 2-3 小时）

---

## 功能优先级总览

```
🔴 高优先级（v1.1.0）
├── 文件移动
├── 回收站
├── 批量操作
└── 文件分享

🟡 中优先级（v1.2.0）
├── 存储统计
├── 分片上传
├── 音频播放
├── PDF预览
└── Office预览

🟢 低优先级（v2.0.0）
├── 用户系统
├── 高级搜索
├── 标签收藏
├── 主题切换
├── 国际化
└── 压缩下载
```

---

## 数据库扩展计划

### v1.1.0 新增表

```sql
-- 回收站（或使用 files 表添加 is_deleted 字段）
CREATE TABLE deleted_files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    original_name VARCHAR(255),
    deleted_at DATETIME,
    expires_at DATETIME
);

-- 文件分享
CREATE TABLE shares (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255),
    expires_at DATETIME,
    created_at DATETIME
);
```

### v2.0.0 新增表

```sql
-- 用户表
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255),
    created_at DATETIME
);

-- 标签表
CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    user_id INTEGER
);

-- 文件标签关联
CREATE TABLE file_tags (
    file_id INTEGER,
    tag_id INTEGER,
    PRIMARY KEY (file_id, tag_id)
);

-- 收藏表
CREATE TABLE favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    user_id INTEGER,
    created_at DATETIME
);
```

---

## API 扩展计划

### v1.1.0 新增接口

| 方法 | 路径 | 功能 |
|------|------|------|
| PUT | `/api/files/{id}/move` | 移动文件 |
| GET | `/api/trash` | 获取回收站列表 |
| POST | `/api/trash/{id}/restore` | 恢复文件 |
| DELETE | `/api/trash/{id}` | 彻底删除 |
| POST | `/api/shares` | 创建分享链接 |
| GET | `/api/shares/{token}` | 获取分享文件 |
| POST | `/api/files/batch-delete` | 批量删除 |
| POST | `/api/files/batch-move` | 批量移动 |

### v1.2.0 新增接口

| 方法 | 路径 | 功能 |
|------|------|------|
| GET | `/api/stats` | 获取存储统计 |
| POST | `/api/upload/chunk` | 分片上传 |
| POST | `/api/upload/merge` | 合并分片 |

---

## 更新日志

- **2026-03-11**: 初始创建版本路线图
