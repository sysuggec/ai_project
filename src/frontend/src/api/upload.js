import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  timeout: 0,
})

export const checkHash = async (hash) => {
  const response = await api.post('/upload/check', { hash })
  return response.data
}

export const uploadFile = async (file, hash, directory, onProgress) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('hash', hash)
  formData.append('directory', directory)

  const response = await api.post('/upload/file', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    onUploadProgress: (e) => {
      if (onProgress && e.total) {
        onProgress(Math.round((e.loaded / e.total) * 100))
      }
    },
  })
  return response.data
}

export const uploadFolder = async (files, paths, directory) => {
  const formData = new FormData()
  
  files.forEach((file, index) => {
    formData.append(`files[${index}]`, file)
    formData.append(`paths[${index}]`, paths[index])
  })
  formData.append('directory', directory)

  const response = await api.post('/upload/folder', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
  return response.data
}

export const getUploadHistory = async (limit = 50, offset = 0) => {
  const response = await api.get('/upload/history', {
    params: { limit, offset },
  })
  return response.data
}
