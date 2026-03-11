<template>
  <div class="resource-page">
    <div class="toolbar">
      <SearchBar v-model="searchQuery" @search="handleSearch" />
      <button class="btn btn-secondary" @click="refreshFiles">
        刷新
      </button>
    </div>

    <div class="content">
      <DirectoryTree
        :directories="directories"
        :selected="selectedDirectory"
        @select="handleDirectorySelect"
        class="sidebar"
      />

      <FileList
        :files="files"
        :loading="loading"
        @download="handleDownload"
        @copyLink="handleCopyLink"
        @delete="handleDelete"
        @rename="handleRename"
        @showLocation="handleShowLocation"
        @preview="handlePreview"
        @play="handlePlay"
        @viewText="handleViewText"
        class="file-list"
      />
    </div>

    <ConfirmDialog ref="confirmDialog" />
    <RenameDialog ref="renameDialog" />
    <ImagePreview ref="imagePreview" />
    <VideoPlayer ref="videoPlayer" />
    <TextViewer ref="textViewer" />
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import SearchBar from '../components/resource/SearchBar.vue'
import DirectoryTree from '../components/resource/DirectoryTree.vue'
import FileList from '../components/resource/FileList.vue'
import ConfirmDialog from '../components/common/ConfirmDialog.vue'
import RenameDialog from '../components/common/RenameDialog.vue'
import ImagePreview from '../components/common/ImagePreview.vue'
import VideoPlayer from '../components/common/VideoPlayer.vue'
import TextViewer from '../components/common/TextViewer.vue'
import { useResource } from '../composables/useResource'
import { getDownloadUrl } from '../api/resource'

const {
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
} = useResource()

const showToast = inject('showToast')
const confirmDialog = ref(null)
const renameDialog = ref(null)
const imagePreview = ref(null)
const videoPlayer = ref(null)
const textViewer = ref(null)

onMounted(async () => {
  await Promise.all([loadDirectories(), loadFiles()])
})

const handleSearch = () => {
  loadFiles(selectedDirectory.value, searchQuery.value)
}

const handleDirectorySelect = (path) => {
  selectedDirectory.value = path
  loadFiles(path, searchQuery.value)
}

const refreshFiles = () => {
  loadFiles(selectedDirectory.value, searchQuery.value)
}

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

.file-list {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

@media (max-width: 768px) {
  .content {
    grid-template-columns: 1fr;
  }
}
</style>
