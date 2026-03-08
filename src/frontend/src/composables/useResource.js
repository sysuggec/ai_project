import { ref } from 'vue'
import { getFiles, getDirectories, deleteFile as deleteFileApi, renameFile as renameFileApi, downloadFile as downloadFileApi, getDownloadUrl } from '../api/resource'

export function useResource() {
  const files = ref([])
  const directories = ref([])
  const loading = ref(false)
  const selectedDirectory = ref(null)
  const searchQuery = ref('')

  const loadFiles = async (directory = null, search = null) => {
    loading.value = true
    try {
      const result = await getFiles(directory, search)
      files.value = result.files || []
    } catch (error) {
      console.error('加载文件失败', error)
      files.value = []
    } finally {
      loading.value = false
    }
  }

  const loadDirectories = async () => {
    try {
      const result = await getDirectories()
      directories.value = result.directories || []
    } catch (error) {
      console.error('加载目录失败', error)
      directories.value = []
    }
  }

  const deleteFile = async (id) => {
    await deleteFileApi(id)
  }

  const renameFile = async (id, name) => {
    await renameFileApi(id, name)
  }

  const downloadFile = (id) => {
    downloadFileApi(id)
  }

  const copyDownloadLink = async (id) => {
    const url = getDownloadUrl(id)
    try {
      await navigator.clipboard.writeText(url)
      return true
    } catch (error) {
      console.error('复制失败', error)
      // Fallback: 使用传统方式复制
      const textArea = document.createElement('textarea')
      textArea.value = url
      textArea.style.position = 'fixed'
      textArea.style.left = '-9999px'
      document.body.appendChild(textArea)
      textArea.select()
      try {
        document.execCommand('copy')
        document.body.removeChild(textArea)
        return true
      } catch (e) {
        document.body.removeChild(textArea)
        return false
      }
    }
  }

  return {
    files,
    directories,
    loading,
    selectedDirectory,
    searchQuery,
    loadFiles,
    loadDirectories,
    deleteFile,
    renameFile,
    downloadFile,
    copyDownloadLink,
  }
}
