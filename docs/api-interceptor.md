# API 响应拦截器规范

> 本文档定义了项目中所有 API 调用的响应拦截器规范，确保前后端数据交互的一致性。

---

## 1. 后端响应格式规范

### 1.1 统一响应结构

所有后端 API 响应必须遵循以下格式：

```json
{
  "success": true|false,
  "data": { ... },
  "error": "错误信息"
}
```

### 1.2 成功响应示例

```json
{
  "success": true,
  "data": {
    "files": [
      { "id": 1, "name": "test.txt", "size": 1024 }
    ]
  }
}
```

### 1.3 错误响应示例

```json
{
  "success": false,
  "error": "文件不存在"
}
```

---

## 2. 前端拦截器实现

### 2.1 标准拦截器代码

所有 API 文件（`resource.js`, `trash.js`, `file.js`, `share.js`, `upload.js`）必须使用以下拦截器：

```javascript
import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

// 统一处理响应
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

### 2.2 API 函数实现规范

#### 错误示例 ❌

```javascript
export const getFiles = async () => {
  const response = await api.get('/files')
  return response.data  // ❌ 拦截器已提取 data
}
```

#### 正确示例 ✅

```javascript
export const getFiles = async () => {
  const response = await api.get('/files')
  return response  // ✅ 直接返回
}
```

---

## 3. 数据流向说明

### 3.1 完整数据流

```
后端响应
  ↓
{
  "success": true,
  "data": { "files": [...] }
}
  ↓
axios 响应对象
  ↓
response.data = {
  "success": true,
  "data": { "files": [...] }
}
  ↓
拦截器处理
  ↓
提取 response.data.data
  ↓
返回给调用者
  ↓
{ "files": [...] }
```

### 3.2 错误处理流

```
后端错误响应
  ↓
{
  "success": false,
  "error": "文件不存在"
}
  ↓
axios 拦截器
  ↓
return Promise.reject(error)
  ↓
Composable try-catch
  ↓
error.message = "Request failed with status code 404"
  ↓
显示错误提示
```

---

## 4. Composable 函数实现规范

### 4.1 错误示例 ❌

```javascript
const fetchTrashList = async () => {
  try {
    const response = await getTrashList()
    // ❌ 拦截器已移除 success 字段
    if (response.success) {
      trashList.value = response.files
    }
  } catch (e) {
    error.value = e.message
  }
}
```

### 4.2 正确示例 ✅

```javascript
const fetchTrashList = async () => {
  try {
    const response = await getTrashList()
    // ✅ 拦截器已返回 { files: [...] }
    trashList.value = response.files || []
  } catch (e) {
    error.value = e.message || '获取回收站列表失败'
  }
}
```

---

## 5. 空值合并运算符 `??` 的使用

### 5.1 为什么使用 `??` 而不是 `||`

```javascript
// 使用 || 的问题
response.data.data || response.data

// 当 data 为空数组 [] 时：
// [] || { success: true, data: [] }
// 结果: { success: true, data: [] }  // ❌ 数据结构不一致

// 使用 ?? 的正确方式
response.data.data ?? response.data

// 当 data 为空数组 [] 时：
// [] ?? { success: true, data: [] }
// 结果: []  // ✅ 保持一致
```

### 5.2 `??` 与 `||` 的区别

| 操作符 | 触发条件 | 示例 |
|--------|---------|------|
| `||` | 左侧为 falsy 值 | `0, '', null, undefined, false, NaN` |
| `??` | 左侧为 null 或 undefined | `null, undefined` |

---

## 6. 调试技巧

### 6.1 检查后端响应

```bash
# 检查 API 响应格式
curl http://localhost:8080/api/files | jq '.'
```

### 6.2 检查拦截器输出

在浏览器控制台中：

```javascript
// 在拦截器中添加调试日志
api.interceptors.response.use(
  response => {
    console.log('原始响应:', response.data)
    const result = response.data.data ?? response.data
    console.log('拦截器输出:', result)
    return result
  }
)
```

### 6.3 常见错误及排查

| 错误信息 | 可能原因 | 解决方案 |
|---------|---------|---------|
| `Cannot read properties of undefined (reading 'files')` | API 函数返回了 `undefined` | 检查是否使用了 `response.data` |
| `response.success is undefined` | Composable 检查了已移除的 `success` 字段 | 移除 `if (response.success)` 检查 |
| `response.files is undefined` | 后端返回的数据字段名不匹配 | 检查后端返回的字段名 |

---

## 7. 最佳实践

### 7.1 类型安全（如果使用 TypeScript）

```typescript
interface ApiResponse<T> {
  success: boolean
  data: T
  error?: string
}

interface FileListResponse {
  files: File[]
}

const api = axios.create({ baseURL: '/api' })

api.interceptors.response.use(
  (response: AxiosResponse<ApiResponse<FileListResponse>>) => {
    if (response.data.success) {
      return response.data.data as FileListResponse
    }
    return response.data.data ?? response.data
  }
)
```

### 7.2 错误处理增强

```javascript
api.interceptors.response.use(
  response => {
    return response.data.data ?? response.data
  },
  error => {
    // 统一错误处理
    const errorMessage = error.response?.data?.error || error.message || '请求失败'
    console.error('API Error:', errorMessage)
    return Promise.reject(new Error(errorMessage))
  }
)
```

### 7.3 请求拦截器（可选）

```javascript
// 添加 token、时间戳等
api.interceptors.request.use(
  config => {
    config.headers['X-Request-Time'] = Date.now()
    return config
  },
  error => Promise.reject(error)
)
```

---

## 8. 检查清单

在修改 API 相关代码时，请检查：

- [ ] 是否为所有 API 文件添加了响应拦截器？
- [ ] API 函数是否直接返回 `response` 而不是 `response.data`？
- [ ] Composable 函数是否直接访问响应数据而不是检查 `response.success`？
- [ ] 是否使用了 `??` 而不是 `||` 来处理空值？
- [ ] 是否在浏览器中强制刷新了缓存（`Ctrl + F5`）？

---

## 9. 相关文档

- [更新日志](changelog.md) - 2026-03-14 拦截器修复记录
- [部署指南](deployment.md) - Docker 部署和常见问题
- [技术方案](solution.md) - API 设计规范

---

**文档版本**: v1.0
**最后更新**: 2026-03-14
**维护者**: 开发团队
