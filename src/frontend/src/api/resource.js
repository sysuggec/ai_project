import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
})

export const getFiles = async (directory = null, search = null) => {
  const params = {}
  if (directory) params.directory = directory
  if (search) params.search = search

  const response = await api.get('/files', { params })
  return response.data
}

export const getDirectories = async () => {
  const response = await api.get('/directories')
  return response.data
}

export const createDirectory = async (path) => {
  const response = await api.post('/directories', { path })
  return response.data
}

export const downloadFile = (id) => {
  window.open(`/api/files/${id}/download`, '_blank')
}

export const deleteFile = async (id) => {
  const response = await api.delete(`/files/${id}`)
  return response.data
}

export const renameFile = async (id, name) => {
  const response = await api.put(`/files/${id}`, { name })
  return response.data
}
