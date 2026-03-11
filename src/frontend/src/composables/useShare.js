import { ref } from 'vue'
import { createShare, getShareList, deleteShare, getShareInfo } from '@/api/share'

/**
 * 文件分享组合式函数
 */
export function useShare() {
  const shareList = ref([])
  const currentShare = ref(null)
  const loading = ref(false)
  const error = ref(null)

  /**
   * 创建分享链接
   */
  const createFileShare = async (fileId, expiresIn = 86400, password = null) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await createShare(fileId, expiresIn, password)
      if (response.success) {
        currentShare.value = response.data
        return { success: true, share: response.data }
      } else {
        error.value = response.error
        return { success: false, error: response.error }
      }
    } catch (e) {
      error.value = e.message || '创建分享链接失败'
      return { success: false, error: e.message }
    } finally {
      loading.value = false
    }
  }

  /**
   * 获取分享列表
   */
  const fetchShareList = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await getShareList()
      if (response.success) {
        shareList.value = response.data.shares
      } else {
        error.value = response.error
      }
    } catch (e) {
      error.value = e.message || '获取分享列表失败'
    } finally {
      loading.value = false
    }
  }

  /**
   * 删除分享
   */
  const deleteFileShare = async (id) => {
    try {
      const response = await deleteShare(id)
      if (response.success) {
        // 从列表中移除
        shareList.value = shareList.value.filter(s => s.id !== id)
        return { success: true, message: response.data.message }
      } else {
        return { success: false, error: response.error }
      }
    } catch (e) {
      return { success: false, error: e.message || '删除分享失败' }
    }
  }

  /**
   * 获取分享文件信息
   */
  const fetchShareInfo = async (token, password = null) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await getShareInfo(token, password)
      if (response.success) {
        currentShare.value = response.data
        return { success: true, info: response.data }
      } else {
        error.value = response.error
        return { success: false, error: response.error }
      }
    } catch (e) {
      error.value = e.message || '获取分享信息失败'
      return { success: false, error: e.message }
    } finally {
      loading.value = false
    }
  }

  /**
   * 清除当前分享
   */
  const clearCurrentShare = () => {
    currentShare.value = null
  }

  return {
    shareList,
    currentShare,
    loading,
    error,
    createShare: createFileShare,
    fetchShareList,
    deleteShare: deleteFileShare,
    fetchShareInfo,
    clearCurrentShare,
  }
}
