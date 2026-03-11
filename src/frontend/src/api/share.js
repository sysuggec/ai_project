import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

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
  return response.data
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
  return response.data
}

/**
 * 获取分享列表
 */
export const getShareList = async () => {
  const response = await api.get('/shares')
  return response.data
}

/**
 * 删除分享
 * @param {number} id - 分享ID
 */
export const deleteShare = async (id) => {
  const response = await api.delete(`/shares/${id}`)
  return response.data
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
