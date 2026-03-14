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
 * 获取回收站文件列表
 */
export const getTrashList = async () => {
  const response = await api.get('/trash')
  return response
}

/**
 * 恢复文件
 * @param {number} id - 回收站记录ID
 */
export const restoreFile = async (id) => {
  const response = await api.post(`/trash/${id}/restore`)
  return response
}

/**
 * 彻底删除文件
 * @param {number} id - 回收站记录ID
 */
export const deletePermanently = async (id) => {
  const response = await api.delete(`/trash/${id}`)
  return response
}

/**
 * 清空回收站
 */
export const clearTrash = async () => {
  const response = await api.delete('/trash')
  return response
}
