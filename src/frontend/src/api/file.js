import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

/**
 * 移动文件到目标目录
 * @param {number} fileId - 文件ID
 * @param {number} targetDirectoryId - 目标目录ID
 */
export const moveFile = async (fileId, targetDirectoryId) => {
  const response = await api.put(`/files/${fileId}/move`, {
    target_directory_id: targetDirectoryId,
  })
  return response.data
}

/**
 * 批量删除文件
 * @param {number[]} fileIds - 文件ID数组
 */
export const batchDeleteFiles = async (fileIds) => {
  const response = await api.post('/files/batch-delete', {
    file_ids: fileIds,
  })
  return response.data
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
  return response.data
}
