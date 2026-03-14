import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

// 统一处理响应
api.interceptors.response.use(
  response => {
    // 如果响应包含 success 字段
    if (response.data && typeof response.data === 'object' && 'success' in response.data) {
      // 如果 success 为 false，抛出错误
      if (response.data.success === false) {
        const error = new Error(response.data.error || '请求失败')
        error.response = response
        error.responseData = response.data
        return Promise.reject(error)
      }
      // 返回 data 字段
      return response.data.data ?? response.data
    }
    return response.data
  },
  error => {
    console.error('API Error:', error)
    // 如果错误响应中包含 success: false，提取错误信息
    if (error.response && error.response.data) {
      const data = error.response.data
      if (data && typeof data === 'object' && 'success' in data && data.success === false) {
        error.responseData = data
        error.message = data.error || error.message
      }
    }
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
 * @param {number} page - 页码
 * @param {number} perPage - 每页数量
 */
export const getShareList = async (page = 1, perPage = 20) => {
  const response = await api.get('/shares', {
    params: { page, perPage }
  })
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
