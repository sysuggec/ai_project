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

/**
 * 创建分享链接
 * @param {number} fileId - 文件ID
 * @param {number} expiresIn - 过期时间（秒）
 * @param {string} password - 访问密码（可选）
 */
export const createShare = async (fileId, expiresIn = 86400, password = null) => {
  const response = await api.post('/shares', {
    file_id: fileId,
    expires_in: expiresIn,
    password,
  })
  return response
}

/**
 * 获取分享文件信息
 * @param {string} token - 分享token
 * @param {string} password - 访问密码（可选）
 */
export const getShareInfo = async (token, password = null) => {
  const response = await api.get(`/shares/${token}`, {
    params: password ? { password } : {},
  })
  return response
}

/**
 * 获取分享列表
 */
export const getShareList = async () => {
  const response = await api.get('/shares')
  return response
}

/**
 * 删除分享
 * @param {number} id - 分享ID
 */
export const deleteShare = async (id) => {
  const response = await api.delete(`/shares/${id}`)
  return response
}

/**
 * 获取分享下载URL
 * @param {string} token - 分享token
 * @param {string} password - 访问密码（可选）
 */
export const getShareDownloadUrl = (token, password = null) => {
  let url = `${window.location.origin}/api/shares/${token}/download`
  if (password) {
    url += `?password=${encodeURIComponent(password)}`
  }
  return url
}
