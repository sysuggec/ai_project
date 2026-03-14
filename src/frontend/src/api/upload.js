import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  timeout: 0,
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

export const checkHash = async (hash) => {
  const response = await api.post('/upload/check', { hash })
  return response
}

export const uploadFile = async (file, hash, directory, onProgress) => {
  const formData = new FormData()

  // 只有在实际有文件时才添加文件
  if (file) {
    formData.append('file', file)
  }
  // 只有 hash 有效时才添加（后端会在 hash 为空时自动计算）
  if (hash) {
    formData.append('hash', hash)
  }
  formData.append('directory', directory)

  const config = {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }

  // 只有在实际文件上传时才跟踪进度
  if (file && onProgress) {
    config.onUploadProgress = (e) => {
      if (e.total) {
        onProgress(Math.round((e.loaded / e.total) * 100))
      }
    }
  }

  const response = await api.post('/upload/file', formData, config)
  return response
}

/**
 * 秒传：只发送哈希和文件名，不发送实际文件
 */
export const instantUpload = async (fileName, hash, directory) => {
  const formData = new FormData()
  formData.append('hash', hash)
  formData.append('directory', directory)
  formData.append('filename', fileName)

  const response = await api.post('/upload/file', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
  return response
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
  return response
}

export const getUploadHistory = async (limit = 50, offset = 0) => {
  const response = await api.get('/upload/history', {
    params: { limit, offset },
  })
  return response
}
