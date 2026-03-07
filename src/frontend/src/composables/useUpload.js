import { ref } from 'vue'
import { checkHash, uploadFile, uploadFolder, instantUpload } from '../api/upload'
import { calculateHash } from '../utils/hash'

export function useUpload() {
  const uploadQueue = ref([])
  const isUploading = ref(false)
  let idCounter = 0

  const addFiles = (files, directory) => {
    for (const file of files) {
      uploadQueue.value.push({
        id: ++idCounter,
        file,
        directory,
        status: 'pending',
        progress: 0,
        hash: null,
        error: null,
      })
    }
  }

  const addFolder = (files, paths, directory) => {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      const relativePath = paths[i]
      uploadQueue.value.push({
        id: ++idCounter,
        file,
        directory,
        relativePath,
        status: 'pending',
        progress: 0,
        hash: null,
        error: null,
      })
    }
  }

  const removeItem = (id) => {
    const index = uploadQueue.value.findIndex(item => item.id === id)
    if (index !== -1) {
      uploadQueue.value.splice(index, 1)
    }
  }

  const clearQueue = () => {
    uploadQueue.value = []
  }

  /**
   * 计算文件的目标目录
   * 如果是文件夹上传（有 relativePath），则拼接相对路径的目录部分
   */
  const getTargetDirectory = (item) => {
    if (item.relativePath) {
      // 获取相对路径的目录部分
      const dirPath = item.relativePath.includes('/') 
        ? item.relativePath.substring(0, item.relativePath.lastIndexOf('/'))
        : ''
      
      // 拼接目标目录和相对路径
      if (dirPath) {
        return item.directory === '/' 
          ? '/' + dirPath 
          : item.directory + '/' + dirPath
      }
    }
    return item.directory
  }

  const processItem = async (item) => {
    try {
      item.status = 'hashing'
      const hash = await calculateHash(item.file)
      item.hash = hash

      const checkResult = await checkHash(hash)
      const targetDirectory = getTargetDirectory(item)
      
      if (checkResult.exists) {
        // 秒传：只发送哈希和文件名，不发送实际文件
        await instantUpload(item.file.name, hash, targetDirectory)
        item.status = 'instant'
        item.progress = 100
        return
      }

      item.status = 'uploading'
      await uploadFile(item.file, hash, targetDirectory, (progress) => {
        item.progress = progress
      })
      
      item.status = 'success'
      item.progress = 100
    } catch (error) {
      item.status = 'error'
      item.error = error.response?.data?.error || error.message || '上传失败'
    }
  }

  const startUpload = async () => {
    if (isUploading.value) return
    
    isUploading.value = true

    const pendingItems = uploadQueue.value.filter(item => item.status === 'pending')
    
    await Promise.all(pendingItems.map(item => processItem(item)))

    isUploading.value = false
  }

  return {
    uploadQueue,
    isUploading,
    addFiles,
    addFolder,
    removeItem,
    clearQueue,
    startUpload,
  }
}
