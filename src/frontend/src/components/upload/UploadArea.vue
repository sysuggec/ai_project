<template>
  <div
    class="upload-area"
    @dragover.prevent="isDragging = true"
    @dragleave="isDragging = false"
    @drop.prevent="handleDrop"
    @click="triggerFileInput"
  >
    <div class="upload-content">
      <div class="upload-icon">📁</div>
      <p class="upload-text">拖拽文件到此处上传</p>
      <p class="upload-hint">或点击选择文件</p>
      <div class="upload-buttons">
        <button class="upload-btn" @click.stop="selectFiles">选择文件</button>
        <button class="upload-btn" @click.stop="selectFolder">选择文件夹</button>
      </div>
    </div>

    <input
      ref="fileInput"
      type="file"
      multiple
      style="display: none"
      @change="handleFileChange"
    />
    <input
      ref="folderInput"
      type="file"
      webkitdirectory
      directory
      multiple
      style="display: none"
      @change="handleFolderChange"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['files-selected', 'folder-selected'])

const isDragging = ref(false)
const fileInput = ref(null)
const folderInput = ref(null)

const triggerFileInput = () => {
  fileInput.value?.click()
}

const selectFiles = () => {
  fileInput.value?.click()
}

const selectFolder = () => {
  folderInput.value?.click()
}

const handleDrop = async (e) => {
  isDragging.value = false
  const items = e.dataTransfer.items
  const files = []
  const paths = []

  const promises = []

  for (let i = 0; i < items.length; i++) {
    const item = items[i]
    if (item.kind === 'file') {
      const entry = item.webkitGetAsEntry?.()
      if (entry) {
        if (entry.isDirectory) {
          // 收集异步遍历的 Promise
          promises.push(traverseDirectory(entry, files, paths))
        } else {
          const file = item.getAsFile()
          files.push(file)
          paths.push(file.name)
        }
      } else {
        const file = item.getAsFile()
        files.push(file)
        paths.push(file.name)
      }
    }
  }

  // 等待所有目录遍历完成
  await Promise.all(promises)

  if (files.length > 0) {
    emit('folder-selected', files, paths)
  }
}

const traverseDirectory = async (dirEntry, files, paths, basePath = '') => {
  const reader = dirEntry.createReader()
  
  // readEntries 可能需要多次调用才能读取完所有条目
  const readAllEntries = async () => {
    const allEntries = []
    let entries = []
    
    do {
      entries = await new Promise((resolve) => {
        reader.readEntries(resolve)
      })
      allEntries.push(...entries)
    } while (entries.length > 0)
    
    return allEntries
  }

  const entries = await readAllEntries()

  for (const entry of entries) {
    if (entry.isFile) {
      const file = await new Promise((resolve) => {
        entry.file(resolve)
      })
      files.push(file)
      paths.push(basePath + entry.name)
    } else if (entry.isDirectory) {
      await traverseDirectory(entry, files, paths, basePath + entry.name + '/')
    }
  }
}

const handleFileChange = (e) => {
  const files = Array.from(e.target.files)
  if (files.length > 0) {
    emit('files-selected', files)
  }
  e.target.value = ''
}

const handleFolderChange = (e) => {
  const files = Array.from(e.target.files)
  const paths = files.map(f => f.webkitRelativePath || f.name)
  if (files.length > 0) {
    emit('folder-selected', files, paths)
  }
  e.target.value = ''
}
</script>

<style scoped>
.upload-area {
  background: #fff;
  border: 2px dashed #d9d9d9;
  border-radius: 12px;
  padding: 48px 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.upload-area:hover,
.upload-area.dragging {
  border-color: #1890ff;
  background: #f0f7ff;
}

.upload-content {
  pointer-events: none;
}

.upload-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.upload-text {
  font-size: 18px;
  color: #333;
  margin-bottom: 8px;
}

.upload-hint {
  font-size: 14px;
  color: #999;
  margin-bottom: 24px;
}

.upload-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
  pointer-events: auto;
}

.upload-btn {
  padding: 12px 24px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-btn:hover {
  background: #40a9ff;
}
</style>
