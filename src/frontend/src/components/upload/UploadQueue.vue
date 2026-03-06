<template>
  <div class="upload-queue">
    <h3 class="title">上传队列 ({{ items.length }} 个文件)</h3>
    <div class="queue-list">
      <div
        v-for="item in items"
        :key="item.id"
        class="queue-item"
      >
        <div class="item-info">
          <span class="item-name">{{ item.file.name }}</span>
          <span class="item-size">{{ formatSize(item.file.size) }}</span>
        </div>
        <div class="item-status">
          <template v-if="item.status === 'pending'">
            <span class="status pending">等待中</span>
          </template>
          <template v-else-if="item.status === 'hashing'">
            <span class="status hashing">计算哈希...</span>
          </template>
          <template v-else-if="item.status === 'uploading'">
            <span class="status uploading">上传中 {{ item.progress }}%</span>
            <div class="progress-bar">
              <div class="progress" :style="{ width: item.progress + '%' }"></div>
            </div>
          </template>
          <template v-else-if="item.status === 'instant'">
            <span class="status instant">秒传成功</span>
          </template>
          <template v-else-if="item.status === 'success'">
            <span class="status success">上传成功</span>
          </template>
          <template v-else-if="item.status === 'error'">
            <span class="status error">{{ item.error || '上传失败' }}</span>
          </template>
        </div>
        <button
          v-if="item.status === 'pending'"
          class="btn-remove"
          @click="$emit('remove', item.id)"
        >
          移除
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

defineEmits(['remove'])

const formatSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.upload-queue {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

.title {
  font-size: 16px;
  color: #333;
  margin-bottom: 16px;
}

.queue-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.queue-item {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 8px;
  gap: 12px;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-name {
  display: block;
  font-size: 14px;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-size {
  font-size: 12px;
  color: #999;
}

.item-status {
  min-width: 120px;
}

.status {
  font-size: 13px;
}

.status.pending {
  color: #999;
}

.status.hashing,
.status.uploading {
  color: #1890ff;
}

.status.instant {
  color: #52c41a;
}

.status.success {
  color: #52c41a;
}

.status.error {
  color: #ff4d4f;
}

.progress-bar {
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
  margin-top: 4px;
  overflow: hidden;
}

.progress {
  height: 100%;
  background: #1890ff;
  transition: width 0.3s;
}

.btn-remove {
  padding: 6px 12px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-remove:hover {
  border-color: #ff4d4f;
  color: #ff4d4f;
}
</style>
