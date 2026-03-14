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
 * 移动文件到目标目录
 * @param {number} fileId - 文件ID
 * @param {number} targetDirectoryId - 目标目录ID
 */
export const moveFile = async (fileId, targetDirectoryId) => {
  const response = await api.put(`/files/${fileId}/move`, {
    target_directory_id: targetDirectoryId,
  })
  return response
}

/**
 * 批量删除文件
 * @param {number[]} fileIds - 文件ID数组
 */
export const batchDeleteFiles = async (fileIds) => {
  const response = await api.post('/files/batch-delete', {
    file_ids: fileIds,
  })
  return response
}

/**
 * 批量移动文件
 * @param {number[]} fileIds - 文件ID数组
 * @param {number} targetDirectoryId - 目标目录ID
 */
export const batchMoveFiles = async (fileIds, targetDirectoryId) => {
  const response = await api.post('/files/batch-move', {
    file_ids: fileIds,
    target_directory_id: targetDirectoryId,
  })
  return response
}
