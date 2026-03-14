<template>
  <div v-if="visible" class="dialog-overlay" @click.self="handleCancel">
    <div class="dialog">
      <div class="dialog-header">
        <h3>创建分享链接</h3>
        <button @click="handleCancel" class="close-btn">&times;</button>
      </div>

      <div class="dialog-content">
        <div v-if="!shareUrl" class="form">
          <div class="form-group">
            <label>文件名</label>
            <div class="file-name">{{ file?.name || '-' }}</div>
          </div>

          <div class="form-group">
            <label>过期时间</label>
            <select v-model="expiresIn">
              <option :value="3600">1 小时</option>
              <option :value="86400">1 天</option>
              <option :value="604800">7 天</option>
              <option :value="2592000">30 天</option>
            </select>
          </div>

          <div class="form-group">
            <label>
              <input type="checkbox" v-model="usePassword" />
              设置访问密码
            </label>
            <input
              v-if="usePassword"
              type="password"
              v-model="password"
              placeholder="请输入访问密码"
            />
          </div>
        </div>

        <div v-else class="share-result">
          <div class="form-group">
            <label>分享链接</label>
            <div class="share-url-container">
              <input type="text" :value="shareUrl" readonly class="share-url-input" />
              <button @click="copyShareUrl" class="btn btn-secondary">复制</button>
            </div>
          </div>

          <div v-if="hasPassword" class="form-group">
            <label>访问密码</label>
            <div class="password-info">{{ password || '(已设置)' }}</div>
          </div>

          <div class="form-group">
            <label>过期时间</label>
            <div class="expire-info">{{ expiresAt }}</div>
          </div>
        </div>
      </div>

      <div class="dialog-footer">
        <button @click="handleCancel" class="btn btn-secondary">
          {{ shareUrl ? '关闭' : '取消' }}
        </button>
        <button
          v-if="!shareUrl"
          @click="handleCreate"
          class="btn btn-primary"
          :disabled="loading || (usePassword && !password)"
        >
          {{ loading ? '创建中...' : '创建' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useShare } from '@/composables/useShare'
import { useToast } from '@/composables/useToast'

const emit = defineEmits(['cancel', 'created'])

const { createShare, loading, error } = useShare()
const { showToast } = useToast()

const visible = ref(false)
const file = ref(null)
const expiresIn = ref(86400)
const usePassword = ref(false)
const password = ref('')
const shareUrl = ref('')
const expiresAt = ref('')
const hasPassword = ref(false)

const handleCreate = async () => {
  if (!file.value) return

  const result = await createShare(
    file.value.id,
    expiresIn.value,
    usePassword.value ? password.value : null
  )

  if (result.success) {
    shareUrl.value = window.location.origin + result.share.share_url
    expiresAt.value = result.share.expires_at
    hasPassword.value = result.share.has_password
    showToast('分享链接创建成功', 'success')
    emit('created', result.share)
  } else {
    showToast(result.error || '创建失败', 'error')
  }
}

const copyShareUrl = async () => {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
    showToast('链接已复制到剪贴板', 'success')
  } catch (e) {
    showToast('复制失败，请手动复制', 'error')
  }
}

const handleCancel = () => {
  emit('cancel')
  hide()
}

const show = (fileData) => {
  file.value = fileData
  visible.value = true
  // 重置表单
  expiresIn.value = 86400
  usePassword.value = false
  password.value = ''
  shareUrl.value = ''
  expiresAt.value = ''
  hasPassword.value = false
}

const hide = () => {
  visible.value = false
}

// 暴露方法给父组件
defineExpose({
  show,
  hide
})
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.dialog {
  background: white;
  border-radius: 8px;
  min-width: 450px;
  max-width: 600px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e0e0e0;
}

.dialog-header h3 {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #757575;
  padding: 0;
  line-height: 1;
}

.close-btn:hover {
  color: #333;
}

.dialog-content {
  padding: 20px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.form-group input[type="text"],
.form-group input[type="password"],
.form-group select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #1976d2;
}

.file-name {
  padding: 8px 12px;
  background: #f5f5f5;
  border-radius: 4px;
  font-size: 14px;
}

.share-result {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.share-url-container {
  display: flex;
  gap: 8px;
}

.share-url-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background: #f5f5f5;
}

.password-info,
.expire-info {
  padding: 8px 12px;
  background: #f5f5f5;
  border-radius: 4px;
  font-size: 14px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 20px;
  border-top: 1px solid #e0e0e0;
}

.btn {
  padding: 8px 20px;
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

.btn-primary {
  background: #1976d2;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1565c0;
}

.btn-secondary {
  background: #757575;
  color: white;
}

.btn-secondary:hover {
  background: #616161;
}
</style>
