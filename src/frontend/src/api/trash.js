import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

/**
 * 获取回收站文件列表
 */
export const getTrashList = async () => {
  const response = await api.get('/trash')
  return response.data
}

/**
 * 恢复文件
 * @param {number} id - 回收站记录ID
 */
export const restoreFile = async (id) => {
  const response = await api.post(`/trash/${id}/restore`)
  return response.data
}

/**
 * 彻底删除文件
 * @param {number} id - 回收站记录ID
 */
export const deletePermanently = async (id) => {
  const response = await api.delete(`/trash/${id}`)
  return response.data
}

/**
 * 清空回收站
 */
export const clearTrash = async () => {
  const response = await api.delete('/trash')
  return response.data
}
