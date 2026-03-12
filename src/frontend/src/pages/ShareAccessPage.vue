<template>
  <div class="share-access-page">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>加载中...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <div class="error-icon">❌</div>
      <h2>访问失败</h2>
      <p class="error-message">{{ error }}</p>
      <button @click="goHome" class="btn btn-primary">返回首页</button>
    </div>

    <div v-else-if="needPassword" class="password-form-container">
      <div class="card">
        <h2>文件分享</h2>
        <p class="file-info">此文件需要密码访问</p>
        <form @submit.prevent="handlePasswordSubmit">
          <div class="form-group">
            <label>访问密码</label>
            <input
              type="password"
              v-model="password"
              placeholder="请输入访问密码"
              required
            />
          </div>
          <button type="submit" class="btn btn-primary">确认</button>
        </form>
      </div>
    </div>

    <div v-else class="share-info-container">
      <div class="card">
        <h2>文件分享</h2>
        <div class="file-info-box">
          <div class="info-row">
            <span class="label">文件名：</span>
            <span class="value">{{ fileInfo.file_name }}</span>
          </div>
          <div class="info-row">
            <span class="label">文件大小：</span>
            <span class="value">{{ formatSize(fileInfo.file_size) }}</span>
          </div>
        </div>
        <button @click="handleDownload" class="btn btn-primary btn-large">
          下载文件
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useShare } from '@/composables/useShare'

const route = useRoute()
const { fetchShareInfo } = useShare()

const loading = ref(true)
const error = ref(null)
const needPassword = ref(false)
const password = ref('')
const fileInfo = ref({})
const token = ref('')

onMounted(async () => {
  // 从路由参数获取 token
  token.value = route.params.token

  if (!token.value) {
    error.value = '无效的分享链接'
    loading.value = false
    return
  }

  // 尝试获取文件信息
  await loadFileInfo()
})

const loadFileInfo = async (pwd = null) => {
  loading.value = true
  error.value = null

  try {
    const result = await fetchShareInfo(token.value, pwd)

    if (result.success) {
      fileInfo.value = result.info
      needPassword.value = false
    } else {
      // 检查是否需要密码
      if (result.error === '密码错误' || result.error.includes('密码')) {
        needPassword.value = true
      } else {
        error.value = result.error
      }
    }
  } catch (e) {
    error.value = '获取分享信息失败'
  } finally {
    loading.value = false
  }
}

const handlePasswordSubmit = async () => {
  if (!password.value) {
    return
  }

  await loadFileInfo(password.value)
}

const handleDownload = () => {
  // 构建下载 URL
  let downloadUrl = `${window.location.origin}/api/shares/${token.value}/download`
  if (password.value) {
    downloadUrl += `?password=${encodeURIComponent(password.value)}`
  }

  // 打开下载链接
  window.open(downloadUrl, '_blank')
}

const goHome = () => {
  window.location.href = '/'
}

const formatSize = (bytes) => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.share-access-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.loading {
  text-align: center;
  color: white;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  margin: 0 auto 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-container {
  text-align: center;
  color: white;
}

.error-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.error-container h2 {
  margin: 0 0 16px;
  font-size: 28px;
}

.error-message {
  font-size: 16px;
  margin-bottom: 24px;
  opacity: 0.9;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  min-width: 400px;
  max-width: 500px;
}

.card h2 {
  margin: 0 0 24px;
  font-size: 24px;
  color: #333;
  text-align: center;
}

.password-form-container .file-info {
  text-align: center;
  color: #666;
  margin-bottom: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.file-info-box {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
  border-bottom: none;
}

.label {
  color: #666;
  font-size: 14px;
}

.value {
  color: #333;
  font-size: 14px;
  font-weight: 500;
}

.btn {
  width: 100%;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-large {
  padding: 16px 24px;
  font-size: 18px;
}

@media (max-width: 600px) {
  .card {
    min-width: auto;
    width: 100%;
    padding: 24px;
  }
}
</style>
