import { ref, computed } from 'vue'

/**
 * 批量操作组合式函数
 */
export function useBatchOperation() {
  const selectedFiles = ref([])
  const isSelectAll = ref(false)

  /**
   * 选择文件
   */
  const selectFile = (file) => {
    const index = selectedFiles.value.findIndex(f => f.id === file.id)
    if (index === -1) {
      selectedFiles.value.push(file)
    }
  }

  /**
   * 取消选择文件
   */
  const deselectFile = (fileId) => {
    const index = selectedFiles.value.findIndex(f => f.id === fileId)
    if (index !== -1) {
      selectedFiles.value.splice(index, 1)
    }
  }

  /**
   * 切换文件选择状态
   */
  const toggleFile = (file) => {
    const index = selectedFiles.value.findIndex(f => f.id === file.id)
    if (index === -1) {
      selectedFiles.value.push(file)
    } else {
      selectedFiles.value.splice(index, 1)
    }
  }

  /**
   * 全选
   */
  const selectAll = (files) => {
    selectedFiles.value = [...files]
    isSelectAll.value = true
  }

  /**
   * 取消全选
   */
  const deselectAll = () => {
    selectedFiles.value = []
    isSelectAll.value = false
  }

  /**
   * 清除选择
   */
  const clearSelection = () => {
    selectedFiles.value = []
    isSelectAll.value = false
  }

  /**
   * 检查文件是否已选中
   */
  const isSelected = (fileId) => {
    return selectedFiles.value.some(f => f.id === fileId)
  }

  /**
   * 选中的文件ID列表
   */
  const selectedFileIds = computed(() => {
    return selectedFiles.value.map(f => f.id)
  })

  /**
   * 选中的文件数量
   */
  const selectedCount = computed(() => {
    return selectedFiles.value.length
  })

  /**
   * 是否有选中的文件
   */
  const hasSelection = computed(() => {
    return selectedFiles.value.length > 0
  })

  return {
    selectedFiles,
    isSelectAll,
    selectFile,
    deselectFile,
    toggleFile,
    selectAll,
    deselectAll,
    clearSelection,
    isSelected,
    selectedFileIds,
    selectedCount,
    hasSelection,
  }
}
