<template>
  <div class="upload-page">
    <div class="upload-section">
      <UploadArea
        @files-selected="handleFilesSelected"
        @folder-selected="handleFolderSelected"
      />

      <DirectoryPicker
        v-model="targetDirectory"
        class="directory-picker"
      />
    </div>

    <UploadQueue
      v-if="uploadQueue.length > 0"
      :items="uploadQueue"
      @remove="removeFromQueue"
    />

    <div class="actions" v-if="uploadQueue.length > 0">
      <button class="btn btn-primary" @click="startUpload" :disabled="isUploading">
        {{ isUploading ? '上传中...' : '开始上传' }}
      </button>
      <button class="btn btn-secondary" @click="clearQueue" :disabled="isUploading">
        清空队列
      </button>
    </div>

    <UploadHistory v-if="showHistory" class="history-section" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import UploadArea from '../components/upload/UploadArea.vue'
import DirectoryPicker from '../components/upload/DirectoryPicker.vue'
import UploadQueue from '../components/upload/UploadQueue.vue'
import UploadHistory from '../components/upload/UploadHistory.vue'
import { useUpload } from '../composables/useUpload'
import { inject } from 'vue'

const targetDirectory = ref('/')
const { uploadQueue, isUploading, addFiles, addFolder, startUpload: doUpload, removeItem, clearQueue } = useUpload()
const showToast = inject('showToast')
const showHistory = ref(true)

const handleFilesSelected = (files) => {
  addFiles(files, targetDirectory.value)
}

const handleFolderSelected = (files, paths) => {
  addFolder(files, paths, targetDirectory.value)
}

const removeFromQueue = (id) => {
  removeItem(id)
}

const startUpload = async () => {
  try {
    await doUpload()
    showToast('上传完成', 'success')
  } catch (error) {
    showToast(error.message, 'error')
  }
}
</script>

<style scoped>
.upload-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.upload-section {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 24px;
}

.directory-picker {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn {
  padding: 12px 32px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #1890ff;
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: #40a9ff;
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover:not(:disabled) {
  background: #e0e0e0;
}

.history-section {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

@media (max-width: 768px) {
  .upload-section {
    grid-template-columns: 1fr;
  }
}
</style>
