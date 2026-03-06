<template>
  <div class="upload-history">
    <h3 class="title">上传历史</h3>
    <div class="history-list" v-if="histories.length > 0">
      <div
        v-for="item in histories"
        :key="item.id"
        class="history-item"
      >
        <div class="item-info">
          <span class="item-name">{{ item.original_name }}</span>
          <span class="item-meta">
            {{ formatSize(item.file_size) }} · {{ item.target_directory }}
          </span>
        </div>
        <div class="item-tags">
          <span v-if="item.is_instant_upload" class="tag instant">秒传</span>
          <span :class="['tag', item.status]">
            {{ item.status === 'success' ? '成功' : '失败' }}
          </span>
        </div>
        <span class="item-time">{{ item.upload_time }}</span>
      </div>
    </div>
    <div v-else class="empty">暂无上传记录</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getUploadHistory } from '../../api/upload'

const histories = ref([])

onMounted(async () => {
  await loadHistory()
})

const loadHistory = async () => {
  try {
    const result = await getUploadHistory()
    histories.value = result.histories
  } catch (error) {
    console.error('加载历史失败', error)
  }
}

const formatSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.upload-history {
  margin-top: 24px;
}

.title {
  font-size: 16px;
  color: #333;
  margin-bottom: 16px;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.history-item {
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

.item-meta {
  font-size: 12px;
  color: #999;
}

.item-tags {
  display: flex;
  gap: 6px;
}

.tag {
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.tag.instant {
  background: #e6f7ff;
  color: #1890ff;
}

.tag.success {
  background: #f6ffed;
  color: #52c41a;
}

.tag.error {
  background: #fff2f0;
  color: #ff4d4f;
}

.item-time {
  font-size: 12px;
  color: #999;
  white-space: nowrap;
}

.empty {
  text-align: center;
  color: #999;
  padding: 24px;
}
</style>
