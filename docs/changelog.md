# 更新日志

本文档记录项目的重大更新、修复和改进。

---

## 2026-03-22 - 部署模式优化

### 新增功能

#### 1. 开发/生产环境部署模式

新增 `deploy.sh` 脚本的多环境支持，可通过 `--mode` 参数切换部署模式：

**开发模式** (`--mode dev`)
- 源代码挂载到容器，修改后立即可见
- 适合日常开发和快速调试
- 修改后端代码无需重启容器
- 修改前端代码需重新构建：`cd src/frontend && npm run build`

**生产模式** (`--mode prod` 或默认)
- 使用 `Dockerfile.cached` 多阶段构建
- 代码打包到镜像中，部署更稳定
- 使用预构建基础镜像加速构建（~30秒）
- 适合生产环境部署

#### 2. 新增文件

- `docker-compose.dev.yml` - 开发环境配置文件
  - 挂载源代码：`./src/backend:/var/www/html`
  - 使用普通 `Dockerfile`（非缓存版本）
  - 容器名称：`resource-system-dev`

#### 3. 命令扩展

所有 `deploy.sh` 命令均支持 `--mode` 参数：

```bash
# 开发模式
./deploy.sh start --mode dev
./deploy.sh stop --mode dev
./deploy.sh logs --mode dev

# 生产模式（默认）
./deploy.sh start
./deploy.sh stop
./deploy.sh logs
```

### 改进

- 移除生产环境源代码挂载，改用容器内代码部署
- 统一响应拦截器配置，确保 API 数据正确返回
- 更新文档说明开发/生产模式的区别和使用场景

### 使用建议

| 场景 | 推荐模式 | 命令 |
|------|---------|------|
| 日常开发 | 开发模式 | `./deploy.sh start --mode dev` |
| 生产部署 | 生产模式 | `./deploy.sh start` |
| 快速调试 | 开发模式 | `./deploy.sh start --mode dev` |
| 测试环境 | 生产模式 | `./deploy.sh start --mode prod` |

详细说明请参考 [部署指南](deployment.md)。

---

## 2026-03-14 - API 响应拦截器修复

### 问题描述

项目在使用 Docker 部署后，前端页面无法正常加载数据，浏览器控制台报错：
- `TypeError: Cannot read properties of undefined (reading 'directories')`
- `TypeError: Cannot read properties of undefined (reading 'files')`

### 问题根因

所有 API 文件（`resource.js`, `trash.js`, `file.js`, `share.js`, `upload.js`）都创建了独立的 axios 实例，但没有配置统一的响应拦截器。导致：

1. **数据结构不匹配**：
   - 后端返回：`{ success: true, data: { files: [...] } }`
   - 拦截器未配置：`response.data` 返回整个响应对象
   - 前端期望：直接访问 `response.files`

2. **重复访问 `.data`**：
   - 拦截器提取 `response.data.data` 后
   - API 函数又访问 `response.data`
   - 导致最终返回 `undefined`

### 修复内容

#### 1. 统一所有 API 文件的响应拦截器

为以下文件添加统一的响应拦截器逻辑：

```javascript
api.interceptors.response.use(
  response => {
    // 如果响应包含 success 字段且为 true，返回 data 字段
    if (response.data && typeof response.data === 'object' && 'success' in response.data) {
      // 返回 data 字段，如果 data 不存在则返回整个 response.data
      return response.data.data ?? response.data
    }
    return response.data
  },
  error => {
    console.error('API Error:', error)
    return Promise.reject(error)
  }
)
```

#### 2. 移除 API 函数中的 `.data` 访问

修改前：
```javascript
export const getFiles = async (directory = null, search = null) => {
  const response = await api.get('/files', { params })
  return response.data  // ❌ 拦截器已提取 data
}
```

修改后：
```javascript
export const getFiles = async (directory = null, search = null) => {
  const response = await api.get('/files', { params })
  return response  // ✅ 直接返回
}
```

#### 3. 更新 Composable 函数的数据访问逻辑

修改前：
```javascript
const response = await getTrashList()
if (response.success) {  // ❌ 拦截器已移除 success
  trashList.value = response.files
}
```

修改后：
```javascript
const response = await getTrashList()
// 拦截器已返回 { files: [...] }
trashList.value = response.files || []
```

### 修复的文件

**API 文件**：
- `/workspace/src/frontend/src/api/resource.js`
- `/workspace/src/frontend/src/api/trash.js`
- `/workspace/src/frontend/src/api/file.js`
- `/workspace/src/frontend/src/api/share.js`
- `/workspace/src/frontend/src/api/upload.js`

**Composable 文件**：
- `/workspace/src/frontend/src/composables/useTrash.js`
- `/workspace/src/frontend/src/composables/useShare.js`

### 技术细节

#### 空值合并运算符 `??` vs 逻辑或 `||`

使用 `??` 而不是 `||` 的原因：

```javascript
// 使用 || 的问题
response.data.data || response.data
// 当 data 为空数组 [] 时，会回退到 response.data
// 导致数据结构不一致

// 使用 ?? 的正确方式
response.data.data ?? response.data
// 只有当 data 为 null 或 undefined 时才回退
// 空数组 [] 不会触发回退
```

### 影响范围

- ✅ 资源管理页面 (`/resources`) - 目录和文件列表正常加载
- ✅ 回收站页面 (`/trash`) - 删除文件列表正常显示
- ✅ 分享管理页面 (`/shares`) - 分享列表正常加载
- ✅ 文件上传功能 - 上传接口调用正常
- ✅ 批量操作 - 批量删除/移动功能正常

### 部署验证

```bash
# 重新构建前端
cd src/frontend && npm run build

# 重新部署 Docker
cd /workspace && ./deploy.sh rebuild

# 验证服务状态
docker ps --filter "name=resource-system"
```

### 浏览器缓存清理

由于 JS 文件名哈希变化，需要强制刷新浏览器缓存：

- **Windows/Linux**: `Ctrl + F5` 或 `Ctrl + Shift + R`
- **Mac**: `Cmd + Shift + R`

### 后续优化建议

1. **统一 API 实例**：考虑创建一个全局的 axios 实例，避免在多个文件中重复配置拦截器
2. **类型定义**：使用 TypeScript 定义 API 响应的类型，提高代码可维护性
3. **错误处理**：增强拦截器的错误处理逻辑，统一错误提示
4. **请求重试**：在拦截器中添加自动重试机制，提升网络稳定性

---

## 2026-03-11 - 项目初始化

### 初始功能

- 文件上传下载
- SHA-256 秒传
- 文件预览（图片、视频、文本）
- 文件夹拖拽上传
- 文件搜索

### 技术栈

- **前端**: Vue 3 + Composition API + Vite
- **后端**: PHP 8.0+ + Eloquent ORM（自定义轻量级框架）
- **数据库**: SQLite
- **HTTP**: Symfony HttpFoundation
- **E2E 测试**: Playwright

---

**文档版本**: v1.0
**最后更新**: 2026-03-14
