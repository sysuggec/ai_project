<template>
  <div class="file-list">
    <div v-if="loading" class="loading">加载中...</div>
    <div v-else-if="files.length === 0" class="empty">暂无文件</div>
    <div v-else class="files">
      <div
        v-for="file in files"
        :key="file.id"
        class="file-item"
      >
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
defineProps({
  files: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['download', 'copyLink', 'delete', 'rename', 'showLocation', 'preview', 'play', 'viewText'])

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

.file-item {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 8px;
  gap: 12px;
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
