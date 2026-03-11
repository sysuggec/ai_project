<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="close">
      <div class="viewer-container">
        <button class="close-btn" @click="close">×</button>
        <div class="viewer-header">
          <span class="file-name">{{ fileName }}</span>
          <div class="header-actions">
            <div class="view-modes" v-if="isMarkdown && !isEditing">
              <button
                :class="['mode-btn', { active: viewMode === 'preview' }]"
                @click="viewMode = 'preview'"
              >
                预览
              </button>
              <button
                :class="['mode-btn', { active: viewMode === 'source' }]"
                @click="viewMode = 'source'"
              >
                源码
              </button>
            </div>
            <button
              v-if="!isEditing"
              class="edit-btn"
              @click="startEdit"
              title="编辑"
            >
              ✏️ 编辑
            </button>
            <template v-else>
              <button class="cancel-btn" @click="cancelEdit">取消</button>
              <button class="save-btn" @click="saveContent" :disabled="saving">
                {{ saving ? '保存中...' : '保存' }}
              </button>
            </template>
          </div>
        </div>
        <div class="content-wrapper">
          <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
          </div>
          <div v-else-if="error" class="error-overlay">
            {{ error }}
          </div>
          <template v-else>
            <textarea
              v-if="isEditing"
              v-model="editContent"
              class="edit-textarea"
              placeholder="输入内容..."
            ></textarea>
            <template v-else>
              <pre v-if="!isMarkdown || viewMode === 'source'" class="text-content">{{ content }}</pre>
              <div v-else class="markdown-content" v-html="renderedMarkdown"></div>
            </template>
          </template>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'
import { updateFileContent } from '../../api/resource'

const visible = ref(false)
const content = ref('')
const fileName = ref('')
const fileId = ref(null)
const loading = ref(false)
const error = ref('')
const viewMode = ref('preview')
const isEditing = ref(false)
const editContent = ref('')
const saving = ref(false)

const emit = defineEmits(['saved'])

const isMarkdown = computed(() => {
  const name = fileName.value.toLowerCase()
  return name.endsWith('.md') || name.endsWith('.markdown')
})

const renderedMarkdown = computed(() => {
  if (!content.value) return ''
  return renderMarkdown(content.value)
})

// 简单的 Markdown 渲染器
const renderMarkdown = (text) => {
  let html = text
    // 转义 HTML
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    // 代码块
    .replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code class="language-$1">$2</code></pre>')
    // 行内代码
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    // 标题
    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
    .replace(/^# (.+)$/gm, '<h1>$1</h1>')
    // 粗体和斜体
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    // 链接
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
    // 无序列表
    .replace(/^- (.+)$/gm, '<li>$1</li>')
    // 有序列表
    .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
    // 引用
    .replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>')
    // 水平线
    .replace(/^---$/gm, '<hr>')
    // 段落
    .replace(/\n\n/g, '</p><p>')
    // 换行
    .replace(/\n/g, '<br>')

  // 包装列表
  html = html.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
  // 清理重复的 ul 标签
  html = html.replace(/<\/ul>\s*<ul>/g, '')

  return `<div class="markdown-body"><p>${html}</p></div>`
}

const show = async (url, name, id) => {
  fileName.value = name
  fileId.value = id
  visible.value = true
  loading.value = true
  error.value = ''
  content.value = ''
  viewMode.value = 'preview'
  isEditing.value = false

  try {
    const response = await fetch(url)
    if (!response.ok) {
      throw new Error('加载失败')
    }
    const text = await response.text()
    content.value = text
    editContent.value = text
  } catch (e) {
    error.value = '无法加载文件内容'
  } finally {
    loading.value = false
  }
}

const startEdit = () => {
  editContent.value = content.value
  isEditing.value = true
}

const cancelEdit = () => {
  editContent.value = content.value
  isEditing.value = false
}

const saveContent = async () => {
  if (saving.value || !fileId.value) return

  saving.value = true
  try {
    await updateFileContent(fileId.value, editContent.value)
    content.value = editContent.value
    isEditing.value = false
    emit('saved')
  } catch (e) {
    error.value = '保存失败，请重试'
  } finally {
    saving.value = false
  }
}

const close = () => {
  if (isEditing.value) {
    if (!confirm('有未保存的更改，确定要关闭吗？')) {
      return
    }
  }
  visible.value = false
  isEditing.value = false
}

defineExpose({ show })
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.viewer-container {
  position: relative;
  width: 90vw;
  max-width: 900px;
  max-height: 90vh;
  background: #fff;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 32px;
  height: 32px;
  border: none;
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  font-size: 24px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  z-index: 1;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.2);
}

.viewer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #eee;
}

.file-name {
  font-size: 16px;
  font-weight: 500;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 40%;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.view-modes {
  display: flex;
  gap: 4px;
  background: #f0f0f0;
  padding: 4px;
  border-radius: 8px;
}

.mode-btn {
  padding: 6px 16px;
  border: none;
  background: transparent;
  color: #666;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.mode-btn.active {
  background: #fff;
  color: #333;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.mode-btn:hover:not(.active) {
  color: #333;
}

.edit-btn {
  padding: 6px 16px;
  border: 1px solid #1890ff;
  background: #fff;
  color: #1890ff;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.edit-btn:hover {
  background: #e6f7ff;
}

.cancel-btn {
  padding: 6px 16px;
  border: 1px solid #d9d9d9;
  background: #fff;
  color: #666;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-btn:hover {
  border-color: #40a9ff;
  color: #40a9ff;
}

.save-btn {
  padding: 6px 16px;
  border: none;
  background: #1890ff;
  color: #fff;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.save-btn:hover:not(:disabled) {
  background: #40a9ff;
}

.save-btn:disabled {
  background: #d9d9d9;
  cursor: not-allowed;
}

.content-wrapper {
  flex: 1;
  overflow: auto;
  padding: 20px;
  min-height: 200px;
}

.loading-overlay,
.error-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #999;
}

.error-overlay {
  color: #f56c6c;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f0f0f0;
  border-top-color: #1890ff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.edit-textarea {
  width: 100%;
  min-height: 400px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 16px;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 13px;
  line-height: 1.6;
  resize: vertical;
  outline: none;
  transition: border-color 0.2s;
}

.edit-textarea:focus {
  border-color: #1890ff;
}

.text-content {
  margin: 0;
  padding: 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 13px;
  line-height: 1.6;
  white-space: pre-wrap;
  word-wrap: break-word;
  color: #333;
  background: transparent;
}

.markdown-content {
  font-size: 14px;
  line-height: 1.8;
  color: #333;
}

.markdown-content :deep(h1) {
  font-size: 28px;
  margin: 24px 0 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #eee;
}

.markdown-content :deep(h2) {
  font-size: 22px;
  margin: 20px 0 12px;
}

.markdown-content :deep(h3) {
  font-size: 18px;
  margin: 16px 0 8px;
}

.markdown-content :deep(p) {
  margin: 12px 0;
}

.markdown-content :deep(code) {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 13px;
}

.markdown-content :deep(pre) {
  background: #f5f5f5;
  padding: 16px;
  border-radius: 8px;
  overflow-x: auto;
  margin: 16px 0;
}

.markdown-content :deep(pre code) {
  background: transparent;
  padding: 0;
}

.markdown-content :deep(ul) {
  margin: 12px 0;
  padding-left: 24px;
}

.markdown-content :deep(li) {
  margin: 4px 0;
}

.markdown-content :deep(blockquote) {
  margin: 16px 0;
  padding: 8px 16px;
  border-left: 4px solid #1890ff;
  background: #f5f5f5;
  color: #666;
}

.markdown-content :deep(a) {
  color: #1890ff;
  text-decoration: none;
}

.markdown-content :deep(a:hover) {
  text-decoration: underline;
}

.markdown-content :deep(hr) {
  border: none;
  border-top: 1px solid #eee;
  margin: 24px 0;
}

.markdown-content :deep(strong) {
  font-weight: 600;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .viewer-container,
.modal-leave-to .viewer-container {
  transform: scale(0.9);
}
</style>
