<template>
  <div class="file-list">
    <div v-if="loading" class="loading">加载中...</div>
    <div v-else-if="files.length === 0" class="empty">暂无文件</div>
    <div v-else class="files">
      <!-- 全选控制 -->
      <div class="select-all-bar">
        <label class="checkbox-label">
          <input
            type="checkbox"
            :checked="isAllSelected"
            @change="handleSelectAll"
          />
          <span>全选</span>
        </label>
        <span class="selected-count" v-if="selectedCount > 0">
          已选择 {{ selectedCount }} 个文件
        </span>
      </div>

      <!-- 文件列表 -->
      <div
        v-for="file in files"
        :key="file.id"
        :class="['file-item', { selected: isSelected(file.id) }]"
        draggable="true"
        @dragstart="handleDragStart($event, file)"
      >
        <!-- 复选框 -->
        <label class="checkbox-label">
          <input
            type="checkbox"
            :checked="isSelected(file.id)"
            @change="handleToggleSelect(file)"
          />
        </label>

        <div class="file-icon">
          {{ getFileIcon(file.mime_type) }}
        </div>
        <div class="file-info">
          <span class="file-name">{{ file.name }}</span>
          <span class="file-meta">
            {{ formatSize(file.size) }} · {{ file.upload_time }}
          </span>
        </div>
        <div class="file-actions">
          <button
            v-if="isImage(file.mime_type)"
            class="action-btn"
            @click="$emit('preview', file)"
            title="预览"
          >
            👁️
          </button>
          <button
            v-if="isVideo(file.mime_type)"
            class="action-btn"
            @click="$emit('play', file)"
            title="播放"
          >
            ▶️
          </button>
          <button
            v-if="isTextViewable(file.mime_type, file.name)"
            class="action-btn"
            @click="$emit('viewText', file)"
            title="查看"
          >
            📄
          </button>
          <button class="action-btn" @click="$emit('download', file)" title="下载">
            ⬇️
          </button>
          <button class="action-btn" @click="$emit('copyLink', file)" title="复制下载链接">
            🔗
          </button>
          <button class="action-btn" @click="$emit('showLocation', file)" title="查看物理位置">
            📁
          </button>
          <button class="action-btn" @click="$emit('move', file)" title="移动">
            📂
          </button>
          <button class="action-btn" @click="$emit('share', file)" title="分享">
            📤
          </button>
          <button class="action-btn" @click="$emit('rename', file)" title="重命名">
            ✏️
          </button>
          <button class="action-btn danger" @click="$emit('delete', file)" title="删除">
            🗑️
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  files: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  selectedFiles: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits([
  'download', 'copyLink', 'delete', 'rename', 'showLocation',
  'preview', 'play', 'viewText', 'move', 'share',
  'select', 'select-all', 'drag-start'
])

const isSelected = (fileId) => {
  return props.selectedFiles.some(f => f.id === fileId)
}

const selectedCount = computed(() => props.selectedFiles.length)

const isAllSelected = computed(() => {
  return props.files.length > 0 && props.selectedFiles.length === props.files.length
})

const handleToggleSelect = (file) => {
  emit('select', file)
}

const handleSelectAll = () => {
  if (isAllSelected.value) {
    emit('select-all', [])
  } else {
    emit('select-all', [...props.files])
  }
}

const handleDragStart = (event, file) => {
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', JSON.stringify(file))
  emit('drag-start', file)
}

const isImage = (mimeType) => {
  return mimeType && mimeType.startsWith('image/')
}

const isVideo = (mimeType) => {
  return mimeType && mimeType.startsWith('video/')
}

const isTextViewable = (mimeType, fileName) => {
  if (mimeType && mimeType.startsWith('text/')) return true
  // 检查文件扩展名
  const ext = fileName?.toLowerCase().split('.').pop()
  const textExtensions = ['txt', 'md', 'markdown', 'json', 'js', 'ts', 'vue', 'jsx', 'tsx', 'css', 'scss', 'html', 'xml', 'yaml', 'yml', 'ini', 'conf', 'log', 'sh', 'bash', 'py', 'java', 'c', 'cpp', 'h', 'go', 'rs', 'php', 'sql']
  return textExtensions.includes(ext)
}

const formatSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (mimeType) => {
  if (!mimeType) return '📄'
  if (mimeType.startsWith('image/')) return '🖼️'
  if (mimeType.startsWith('video/')) return '🎬'
  if (mimeType.startsWith('audio/')) return '🎵'
  if (mimeType === 'application/pdf') return '📕'
  if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('tar')) return '📦'
  if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊'
  if (mimeType.includes('word') || mimeType.includes('document')) return '📝'
  return '📄'
}
</script>

<style scoped>
.file-list {
  height: 100%;
}

.loading,
.empty {
  text-align: center;
  padding: 48px 24px;
  color: #999;
}

.files {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.select-all-bar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: #f5f5f5;
  border-radius: 8px;
  margin-bottom: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.selected-count {
  font-size: 14px;
  color: #1976d2;
  font-weight: 500;
}

.file-item {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 8px;
  gap: 12px;
  transition: all 0.2s;
}

.file-item.selected {
  background: #e3f2fd;
  border: 2px solid #1976d2;
}

.file-item:hover {
  background: #f0f0f0;
}

.file-icon {
  font-size: 32px;
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  display: block;
  font-size: 14px;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-meta {
  font-size: 12px;
  color: #999;
}

.file-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: #fff;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 16px;
}

.action-btn:hover {
  background: #e0e0e0;
}

.action-btn.danger:hover {
  background: #fff2f0;
}
</style>
