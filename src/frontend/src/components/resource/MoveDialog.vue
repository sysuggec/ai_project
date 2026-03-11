<template>
  <div v-if="visible" class="dialog-overlay" @click.self="handleCancel">
    <div class="dialog">
      <div class="dialog-header">
        <h3>移动到目录</h3>
        <button @click="handleCancel" class="close-btn">&times;</button>
      </div>

      <div class="dialog-content">
        <div v-if="loading" class="loading">加载中...</div>
        <div v-else class="directory-tree">
          <div
            v-for="dir in directories"
            :key="dir.id"
            :class="['directory-item', { selected: selectedDirectoryId === dir.id }]"
            @click="selectDirectory(dir.id)"
          >
            <span class="directory-icon">📁</span>
            <span class="directory-name">{{ dir.name }}</span>
          </div>
        </div>
      </div>

      <div class="dialog-footer">
        <button @click="handleCancel" class="btn btn-secondary">取消</button>
        <button
          @click="handleConfirm"
          class="btn btn-primary"
          :disabled="!selectedDirectoryId || loading"
        >
          确定
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  directories: {
    type: Array,
    default: () => [],
  },
  currentDirectoryId: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['confirm', 'cancel'])

const selectedDirectoryId = ref(null)
const loading = ref(false)

watch(() => props.visible, (newVal) => {
  if (newVal) {
    // 重置选择
    selectedDirectoryId.value = null
  }
})

const selectDirectory = (id) => {
  selectedDirectoryId.value = id
}

const handleConfirm = () => {
  if (selectedDirectoryId.value) {
    emit('confirm', selectedDirectoryId.value)
  }
}

const handleCancel = () => {
  emit('cancel')
}
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
  min-width: 400px;
  max-width: 600px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
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
  flex: 1;
  overflow-y: auto;
}

.directory-tree {
  max-height: 400px;
}

.directory-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
}

.directory-item:hover {
  background: #f5f5f5;
}

.directory-item.selected {
  background: #e3f2fd;
}

.directory-icon {
  margin-right: 8px;
  font-size: 18px;
}

.directory-name {
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

.loading {
  text-align: center;
  padding: 20px;
  color: #757575;
}
</style>
