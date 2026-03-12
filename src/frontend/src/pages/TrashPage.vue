<template>
  <div class="trash-page">
    <div class="page-header">
      <h2>回收站</h2>
      <div class="header-actions">
        <span class="item-count">{{ trashList.length }} 个文件</span>
        <button
          @click="handleClearTrash"
          class="btn btn-danger"
          :disabled="trashList.length === 0"
        >
          清空回收站
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading">加载中...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else-if="trashList.length === 0" class="empty">
      <p>回收站是空的</p>
    </div>

    <div v-else class="trash-list">
      <div class="list-header">
        <span class="col-file-name">文件名</span>
        <span class="col-original-path">原位置</span>
        <span class="col-file-size">大小</span>
        <span class="col-deleted-at">删除时间</span>
        <span class="col-expires-at">剩余时间</span>
        <span class="col-actions">操作</span>
      </div>

      <div
        v-for="item in trashList"
        :key="item.id"
        class="list-item"
      >
        <span class="col-file-name">{{ item.file_name }}</span>
        <span class="col-original-path">{{ item.original_directory_path }}</span>
        <span class="col-file-size">{{ formatFileSize(item.file_size) }}</span>
        <span class="col-deleted-at">{{ formatDate(item.deleted_at) }}</span>
        <span class="col-expires-at">{{ getRemainingTime(item.expires_at) }}</span>
        <span class="col-actions">
          <button @click="handleRestore(item.id)" class="btn btn-small btn-primary">
            恢复
          </button>
          <button @click="handleDeletePermanently(item.id)" class="btn btn-small btn-danger">
            删除
          </button>
        </span>
      </div>
    </div>

    <ConfirmDialog
      v-if="showConfirmClear"
      :visible="showConfirmClear"
      title="清空回收站"
      message="确定要清空回收站吗？此操作不可恢复。"
      @confirm="confirmClearTrash"
      @cancel="showConfirmClear = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTrash } from '@/composables/useTrash'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const { trashList, loading, error, fetchTrashList, restoreFile, deletePermanently, clearTrash } = useTrash()
const { showToast } = useToast()
const showConfirmClear = ref(false)

onMounted(() => {
  fetchTrashList()
})

const handleRestore = async (id) => {
  const result = await restoreFile(id)
  if (result.success) {
    showToast(result.message || '文件已恢复', 'success')
  } else {
    showToast(result.error || '恢复失败', 'error')
  }
}

const handleDeletePermanently = async (id) => {
  const result = await deletePermanently(id)
  if (result.success) {
    showToast(result.message || '文件已彻底删除', 'success')
  } else {
    showToast(result.error || '删除失败', 'error')
  }
}

const handleClearTrash = () => {
  showConfirmClear.value = true
}

const confirmClearTrash = async () => {
  showConfirmClear.value = false
  const result = await clearTrash()
  if (result.success) {
    showToast(`${result.message} (已删除 ${result.count} 个文件)`, 'success')
  } else {
    showToast(result.error || '清空失败', 'error')
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

const getRemainingTime = (expiresAt) => {
  if (!expiresAt) return '-'
  const now = new Date()
  const expires = new Date(expiresAt)
  const diff = expires - now

  if (diff <= 0) return '已过期'

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  if (days > 0) return `${days} 天`

  const hours = Math.floor(diff / (1000 * 60 * 60))
  if (hours > 0) return `${hours} 小时`

  const minutes = Math.floor(diff / (1000 * 60))
  return `${minutes} 分钟`
}
</script>

<style scoped>
.trash-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  font-size: 24px;
  color: #333;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.item-count {
  color: #757575;
  font-size: 14px;
}

.trash-list {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.list-header {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr 1.5fr;
  gap: 12px;
  padding: 12px 16px;
  background: #f5f5f5;
  font-weight: 500;
  font-size: 14px;
  color: #333;
}

.list-item {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1.5fr 1fr 1.5fr;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  font-size: 14px;
  color: #666;
}

.list-item:last-child {
  border-bottom: none;
}

.list-item:hover {
  background: #f9f9f9;
}

.col-file-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.col-original-path {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.col-actions {
  display: flex;
  gap: 8px;
}

.btn {
  padding: 6px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-small {
  padding: 4px 12px;
  font-size: 12px;
}

.btn-primary {
  background: #1976d2;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1565c0;
}

.btn-danger {
  background: #d32f2f;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c62828;
}

.loading,
.error,
.empty {
  text-align: center;
  padding: 40px 20px;
  color: #757575;
}

.error {
  color: #d32f2f;
}
</style>
