import { ref } from 'vue'
import { getFiles, getDirectories, deleteFile as deleteFileApi, renameFile as renameFileApi, downloadFile as downloadFileApi } from '../api/resource'

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
  }
}
