import { ref } from 'vue'
import { getFiles, getDirectories, deleteFile as deleteFileApi, renameFile as renameFileApi, getDownloadUrl } from '../api/resource'

export function useResource() {
  const files = ref([])
  const directories = ref([])
  const loading = ref(false)
  const selectedDirectory = ref(null)
  const searchQuery = ref('')
  const currentPage = ref(1)
  const perPage = ref(20)
  const total = ref(0)
  const totalPages = ref(0)

  const loadFiles = async (directory = null, search = null, page = 1, pageSize = 20) => {
    loading.value = true
    try {
      const result = await getFiles(directory, search, page, pageSize)
      files.value = result.files || []
      currentPage.value = page
      perPage.value = pageSize
      total.value = result.pagination?.total || 0
      totalPages.value = result.pagination?.last_page || 0
    } catch (error) {
      console.error('加载文件失败', error)
      files.value = []
      total.value = 0
      totalPages.value = 0
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
    currentPage,
    perPage,
    total,
    totalPages,
    loadFiles,
    loadDirectories,
    deleteFile,
    renameFile,
    copyDownloadLink,
  }
}
