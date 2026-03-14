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

  // 分页参数
  const pagination = ref({
    page: 1,
    perPage: 20,
    total: 0,
    totalPages: 0
  })

  /**
   * 创建分享链接
   */
  const createFileShare = async (fileId, expiresIn = 86400, password = null) => {
    loading.value = true
    error.value = null

    try {
      const response = await createShare(fileId, expiresIn, password)
      // 拦截器已经返回了 data 字段
      currentShare.value = response
      return { success: true, share: response }
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
  const fetchShareList = async (page = 1, perPage = 20) => {
    loading.value = true
    error.value = null

    try {
      const response = await getShareList(page, perPage)
      // 拦截器已经返回了 data 字段
      shareList.value = Array.isArray(response.data) ? response.data : []
      pagination.value = {
        page: response.page || 1,
        perPage: response.perPage || 20,
        total: response.total || 0,
        totalPages: response.totalPages || 0
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
      // 拦截器已经返回了 data 字段
      // 从列表中移除
      shareList.value = shareList.value.filter(s => s.id !== id)
      // 更新总数
      if (pagination.value.total > 0) {
        pagination.value.total--
      }
      return { success: true, message: response.message || '删除成功' }
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
      // 拦截器已经返回了 data 字段
      currentShare.value = response
      return { success: true, info: response }
    } catch (e) {
      // 从响应中获取错误信息
      const errorMessage = e.responseData?.error || e.message || '获取分享信息失败'
      error.value = errorMessage
      return { success: false, error: errorMessage }
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
    pagination,
    createShare: createFileShare,
    fetchShareList,
    deleteShare: deleteFileShare,
    fetchShareInfo,
    clearCurrentShare,
  }
}
