<template>
  <div class="share-page">
    <div class="page-header">
      <h2>分享管理</h2>
      <button @click="handleRefresh" class="btn btn-secondary">刷新</button>
    </div>

    <div v-if="loading" class="loading">加载中...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else-if="shareList.length === 0" class="empty">
      <p>暂无分享记录</p>
    </div>

    <div v-else class="share-list">
      <div class="list-header">
        <span class="col-file-name">文件名</span>
        <span class="col-token">Token</span>
        <span class="col-expires-at">过期时间</span>
        <span class="col-download-count">下载次数</span>
        <span class="col-created-at">创建时间</span>
        <span class="col-actions">操作</span>
      </div>

      <div
        v-for="share in shareList"
        :key="share.id"
        class="list-item"
      >
        <span class="col-file-name">{{ share.file_name }}</span>
        <span class="col-token">
          <code>{{ share.token }}</code>
        </span>
        <span class="col-expires-at">
          <span :class="{ 'text-danger': isExpired(share.expires_at) }">
            {{ formatDate(share.expires_at) }}
            {{ isExpired(share.expires_at) ? '(已过期)' : '' }}
          </span>
        </span>
        <span class="col-download-count">{{ share.download_count }}</span>
        <span class="col-created-at">{{ formatDate(share.created_at) }}</span>
        <span class="col-actions">
          <button
            @click="copyShareLink(share.token)"
            class="btn btn-small btn-secondary"
          >
            复制链接
          </button>
          <button
            @click="handleDeleteShare(share.id)"
            class="btn btn-small btn-danger"
          >
            删除
          </button>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useShare } from '@/composables/useShare'
import { useToast } from '@/composables/useToast'

const { shareList, loading, error, fetchShareList, deleteShare } = useShare()
const { showToast } = useToast()

onMounted(() => {
  fetchShareList()
})

const handleRefresh = () => {
  fetchShareList()
}

const handleDeleteShare = async (id) => {
  const result = await deleteShare(id)
  if (result.success) {
    showToast(result.message || '分享已删除', 'success')
  } else {
    showToast(result.error || '删除失败', 'error')
  }
}

const copyShareLink = async (token) => {
  const url = `${window.location.origin}/share/${token}`
  try {
    await navigator.clipboard.writeText(url)
    showToast('分享链接已复制到剪贴板', 'success')
  } catch (e) {
    showToast('复制失败，请手动复制', 'error')
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

const isExpired = (expiresAt) => {
  if (!expiresAt) return false
  return new Date(expiresAt) < new Date()
}
</script>

<style scoped>
.share-page {
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

.share-list {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.list-header {
  display: grid;
  grid-template-columns: 2fr 2fr 1.5fr 1fr 1.5fr 1.5fr;
  gap: 12px;
  padding: 12px 16px;
  background: #f5f5f5;
  font-weight: 500;
  font-size: 14px;
  color: #333;
}

.list-item {
  display: grid;
  grid-template-columns: 2fr 2fr 1.5fr 1fr 1.5fr 1.5fr;
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

.col-token code {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 3px;
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

.btn-secondary {
  background: #757575;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #616161;
}

.btn-danger {
  background: #d32f2f;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c62828;
}

.text-danger {
  color: #d32f2f;
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
