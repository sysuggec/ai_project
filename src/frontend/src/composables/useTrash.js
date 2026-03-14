import { ref } from 'vue'
import { getTrashList, restoreFile, deletePermanently, clearTrash } from '@/api/trash'

/**
 * 回收站组合式函数
 */
export function useTrash() {
  const trashList = ref([])
  const loading = ref(false)
  const error = ref(null)

  /**
   * 获取回收站列表
   */
  const fetchTrashList = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await getTrashList()
      // 拦截器已经返回了 data 字段，所以 response 就是 { files: [...] }
      trashList.value = response.files || []
    } catch (e) {
      error.value = e.message || '获取回收站列表失败'
    } finally {
      loading.value = false
    }
  }

  /**
   * 恢复文件
   */
  const restoreFileFromTrash = async (id) => {
    try {
      const response = await restoreFile(id)
      // 拦截器已经返回了 data 字段
      // 从列表中移除
      trashList.value = trashList.value.filter(f => f.id !== id)
      return { success: true, message: response.message || '文件已恢复' }
    } catch (e) {
      return { success: false, error: e.message || '恢复文件失败' }
    }
  }

  /**
   * 彻底删除文件
   */
  const permanentlyDeleteFile = async (id) => {
    try {
      const response = await deletePermanently(id)
      // 拦截器已经返回了 data 字段
      // 从列表中移除
      trashList.value = trashList.value.filter(f => f.id !== id)
      return { success: true, message: response.message || '文件已彻底删除' }
    } catch (e) {
      return { success: false, error: e.message || '删除文件失败' }
    }
  }

  /**
   * 清空回收站
   */
  const emptyTrash = async () => {
    try {
      const response = await clearTrash()
      // 拦截器已经返回了 data 字段
      trashList.value = []
      return { success: true, message: response.message || '清空成功', count: response.deleted_count }
    } catch (e) {
      return { success: false, error: e.message || '清空回收站失败' }
    }
  }

  return {
    trashList,
    loading,
    error,
    fetchTrashList,
    restoreFile: restoreFileFromTrash,
    deletePermanently: permanentlyDeleteFile,
    clearTrash: emptyTrash,
  }
}
