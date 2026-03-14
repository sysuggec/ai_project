<template>
  <div class="resource-page">
    <div class="toolbar">
      <SearchBar v-model="searchQuery" @search="handleSearch" />
      <button class="btn btn-secondary" @click="refreshFiles">
        刷新
      </button>
    </div>

    <!-- 批量操作工具栏 -->
    <BatchOperationBar
      :visible="hasSelection"
      :selectedFiles="selectedFiles"
      @batch-delete="handleBatchDelete"
      @batch-move="handleBatchMove"
      @batch-download="handleBatchDownload"
      @clear-selection="clearSelection"
    />

    <div class="content">
      <DirectoryTree
        :directories="directories"
        :selected="selectedDirectory"
        @select="handleDirectorySelect"
        class="sidebar"
      />

      <div class="main-content">
        <FileList
          :files="files"
          :loading="loading"
          :selectedFiles="selectedFiles"
          @download="handleDownload"
          @copyLink="handleCopyLink"
          @delete="handleDelete"
          @rename="handleRename"
          @showLocation="handleShowLocation"
          @preview="handlePreview"
          @play="handlePlay"
          @viewText="handleViewText"
          @move="handleMove"
          @share="handleShare"
          @select="handleFileSelect"
          @select-all="handleSelectAll"
        />

        <Pagination
          v-if="!loading"
          :current-page="currentPage"
          :total-pages="totalPages"
          :total="total"
          :per-page="perPage"
          @page-change="handlePageChange"
          @page-size-change="handlePageSizeChange"
        />
      </div>
    </div>

    <ConfirmDialog ref="confirmDialog" />
    <RenameDialog ref="renameDialog" />
    <MoveDialog
      ref="moveDialog"
      :directories="flattenedDirectories"
      @confirm="handleMoveConfirm"
      @cancel="handleMoveCancel"
    />
    <ShareDialog
      ref="shareDialog"
      @cancel="handleShareCancel"
      @created="handleShareCreated"
    />
    <ImagePreview ref="imagePreview" />
    <VideoPlayer ref="videoPlayer" />
    <TextViewer ref="textViewer" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import SearchBar from '../components/resource/SearchBar.vue'
import DirectoryTree from '../components/resource/DirectoryTree.vue'
import FileList from '../components/resource/FileList.vue'
import BatchOperationBar from '../components/resource/BatchOperationBar.vue'
import MoveDialog from '../components/resource/MoveDialog.vue'
import ShareDialog from '../components/resource/ShareDialog.vue'
import ConfirmDialog from '../components/common/ConfirmDialog.vue'
import RenameDialog from '../components/common/RenameDialog.vue'
import ImagePreview from '../components/common/ImagePreview.vue'
import VideoPlayer from '../components/common/VideoPlayer.vue'
import TextViewer from '../components/common/TextViewer.vue'
import Pagination from '../components/common/Pagination.vue'
import { useResource } from '../composables/useResource'
import { useBatchOperation } from '../composables/useBatchOperation'
import { getDownloadUrl, downloadFile, downloadMultipleFiles } from '../api/resource'
import { moveFile, batchDeleteFiles, batchMoveFiles } from '../api/file'

const {
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
} = useResource()

const {
  selectedFiles,
  hasSelection,
  clearSelection,
  selectFile,
  deselectFile,
  selectAll,
} = useBatchOperation()

const showToast = inject('showToast')
const confirmDialog = ref(null)
const renameDialog = ref(null)
const moveDialog = ref(null)
const shareDialog = ref(null)
const imagePreview = ref(null)
const videoPlayer = ref(null)
const textViewer = ref(null)

// 当前操作的文件（移动/分享）
const currentFile = ref(null)
const selectedFilesForMove = ref([])

onMounted(async () => {
  await Promise.all([loadDirectories(), loadFiles(null, null, 1, perPage.value)])
})

// 扁平化目录树用于移动对话框
const flattenedDirectories = computed(() => {
  const result = []

  const flatten = (dirs, level = 0) => {
    if (!Array.isArray(dirs)) return
    dirs.forEach(dir => {
      result.push({
        id: dir.id,
        name: '  '.repeat(level) + dir.name,
        path: dir.path,
      })
      if (Array.isArray(dir.children) && dir.children.length > 0) {
        flatten(dir.children, level + 1)
      }
    })
  }

  flatten(directories.value)
  return result
})

const handleSearch = () => {
  loadFiles(selectedDirectory.value, searchQuery.value, 1, perPage.value)
}

const handleDirectorySelect = (path) => {
  selectedDirectory.value = path
  loadFiles(path, searchQuery.value, 1, perPage.value)
}

const refreshFiles = () => {
  loadFiles(selectedDirectory.value, searchQuery.value, currentPage.value, perPage.value)
}

// 分页处理
const handlePageChange = (page) => {
  loadFiles(selectedDirectory.value, searchQuery.value, page, perPage.value)
}

const handlePageSizeChange = (pageSize) => {
  loadFiles(selectedDirectory.value, searchQuery.value, 1, pageSize)
}

// 文件选择处理
const handleFileSelect = (file) => {
  const index = selectedFiles.value.findIndex(f => f.id === file.id)
  if (index === -1) {
    selectFile(file)
  } else {
    deselectFile(file.id)
  }
}

const handleSelectAll = (files) => {
  if (files.length === 0) {
    clearSelection()
  } else {
    selectAll(files)
  }
}

// 下载处理
const handleDownload = (file) => {
  downloadFile(file.id)
}

const handleCopyLink = async (file) => {
  const success = await copyDownloadLink(file.id)
  if (success) {
    showToast('下载链接已复制', 'success')
  } else {
    showToast('复制失败，请重试', 'error')
  }
}

// 删除处理
const handleDelete = async (file) => {
  const confirmed = await confirmDialog.value?.show(
    '确认删除',
    `确定要删除文件 "${file.name}" 吗？`
  )
  if (confirmed) {
    try {
      await deleteFile(file.id)
      showToast('删除成功', 'success')
      refreshFiles()
    } catch (error) {
      showToast(error.message, 'error')
    }
  }
}

// 重命名处理
const handleRename = async (file) => {
  const newName = await renameDialog.value?.show(file.name)
  if (newName && newName !== file.name) {
    try {
      await renameFile(file.id, newName)
      showToast('重命名成功', 'success')
      refreshFiles()
    } catch (error) {
      showToast(error.message, 'error')
    }
  }
}

// 显示物理位置
const handleShowLocation = async (file) => {
  if (file.storage_path) {
    try {
      await navigator.clipboard.writeText(file.storage_path)
      showToast(`物理路径已复制: ${file.storage_path}`, 'success')
    } catch (error) {
      // Fallback: 显示弹窗
      alert(`文件物理路径:\n${file.storage_path}`)
    }
  } else {
    showToast('无法获取文件物理路径', 'error')
  }
}

// 预览和播放
const handlePreview = (file) => {
  const url = getDownloadUrl(file.id)
  imagePreview.value?.show(url, file.name)
}

const handlePlay = (file) => {
  const url = getDownloadUrl(file.id)
  videoPlayer.value?.show(url, file.name)
}

const handleViewText = (file) => {
  const url = getDownloadUrl(file.id)
  textViewer.value?.show(url, file.name, file.id)
}

// 移动文件
const handleMove = (file) => {
  currentFile.value = file
  moveDialog.value?.show()
}

const handleMoveConfirm = async (targetDirectoryId) => {
  if (!currentFile.value) return

  try {
    // 判断是批量移动还是单个移动
    if (selectedFilesForMove.value && selectedFilesForMove.value.length > 0) {
      // 批量移动
      const result = await batchMoveFiles(
        selectedFilesForMove.value.map(f => f.id),
        targetDirectoryId
      )
      // 安全地访问响应数据
      const successCount = result?.data?.success_count ?? result?.success_count ?? 0
      const failedCount = result?.data?.failed_count ?? result?.failed_count ?? 0
      showToast(
        `移动成功 ${successCount} 个，失败 ${failedCount} 个`,
        failedCount > 0 ? 'warning' : 'success'
      )
      clearSelection()
      selectedFilesForMove.value = []
    } else {
      // 单个移动
      await moveFile(currentFile.value.id, targetDirectoryId)
      showToast('文件移动成功', 'success')
    }
    refreshFiles()
  } catch (error) {
    showToast(error.response?.data?.error || '移动失败', 'error')
  } finally {
    currentFile.value = null
    moveDialog.value?.hide()
  }
}

const handleMoveCancel = () => {
  currentFile.value = null
  moveDialog.value?.hide()
}

// 分享文件
const handleShare = (file) => {
  currentFile.value = file
  shareDialog.value?.show(file)
}

const handleShareCancel = () => {
  currentFile.value = null
  shareDialog.value?.hide()
}

const handleShareCreated = () => {
  currentFile.value = null
  shareDialog.value?.hide()
}

// 批量操作
const handleBatchDelete = async (files) => {
  const confirmed = await confirmDialog.value?.show(
    '确认批量删除',
    `确定要删除选中的 ${files.length} 个文件吗？`
  )

  if (confirmed) {
    try {
      const result = await batchDeleteFiles(files.map(f => f.id))
      // 安全地访问响应数据
      const successCount = result?.data?.success_count ?? result?.success_count ?? 0
      const failedCount = result?.data?.failed_count ?? result?.failed_count ?? 0
      showToast(
        `删除成功 ${successCount} 个，失败 ${failedCount} 个`,
        failedCount > 0 ? 'warning' : 'success'
      )
      clearSelection()
      refreshFiles()
    } catch (error) {
      showToast(error.response?.data?.error || '批量删除失败', 'error')
    }
  }
}

const handleBatchMove = (files) => {
  currentFile.value = files[0] // 用于标记当前操作
  // 保存所有选中的文件
  selectedFilesForMove.value = [...files]
  moveDialog.value?.show()
}

const handleBatchDownload = (files) => {
  downloadMultipleFiles(files.map(f => f.id))
  showToast(`开始下载 ${files.length} 个文件`, 'success')
  clearSelection()
}
</script>

<style scoped>
.resource-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.toolbar {
  display: flex;
  gap: 12px;
  align-items: center;
}

.btn-secondary {
  padding: 10px 20px;
  background: #f0f0f0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

.content {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 16px;
  min-height: 500px;
}

.sidebar {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

.main-content {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

@media (max-width: 768px) {
  .content {
    grid-template-columns: 1fr;
  }
}
</style>
