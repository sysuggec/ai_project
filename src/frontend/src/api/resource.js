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

export const getFiles = async (directory = null, search = null) => {
  const params = {}
  if (directory) params.directory = directory
  if (search) params.search = search

  const response = await api.get('/files', { params })
  return response
}

export const getDirectories = async () => {
  const response = await api.get('/directories')
  return response
}

export const createDirectory = async (path) => {
  const response = await api.post('/directories', { path })
  return response
}

export const getDownloadUrl = (id) => {
  return `${window.location.origin}/api/files/${id}/download`
}

export const downloadFile = (id) => {
  window.open(`/api/files/${id}/download`, '_blank')
}

export const deleteFile = async (id) => {
  const response = await api.delete(`/files/${id}`)
  return response
}

export const renameFile = async (id, name) => {
  const response = await api.put(`/files/${id}`, { name })
  return response
}

export const updateFileContent = async (id, content) => {
  const response = await api.put(`/files/${id}/content`, { content })
  return response
}
