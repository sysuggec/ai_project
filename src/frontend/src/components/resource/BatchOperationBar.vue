<template>
  <div v-if="visible" class="batch-operation-bar">
    <span class="selected-info">已选择 {{ selectedCount }} 个文件</span>
    <div class="operation-buttons">
      <button @click="handleBatchDelete" class="btn btn-danger">批量删除</button>
      <button @click="handleBatchMove" class="btn btn-primary">批量移动</button>
      <button @click="handleBatchDownload" class="btn btn-secondary">批量下载</button>
      <button @click="handleClearSelection" class="btn btn-secondary">取消选择</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  selectedFiles: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['batch-delete', 'batch-move', 'batch-download', 'clear-selection'])

const selectedCount = computed(() => props.selectedFiles.length)

const handleBatchDelete = () => {
  emit('batch-delete', props.selectedFiles)
}

const handleBatchMove = () => {
  emit('batch-move', props.selectedFiles)
}

const handleBatchDownload = () => {
  emit('batch-download', props.selectedFiles)
}

const handleClearSelection = () => {
  emit('clear-selection')
}
</script>

<style scoped>
.batch-operation-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: #e3f2fd;
  border-bottom: 1px solid #bbdefb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.selected-info {
  font-weight: 500;
  color: #1976d2;
}

.operation-buttons {
  display: flex;
  gap: 8px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-primary {
  background: #1976d2;
  color: white;
}

.btn-primary:hover {
  background: #1565c0;
}

.btn-secondary {
  background: #757575;
  color: white;
}

.btn-secondary:hover {
  background: #616161;
}

.btn-danger {
  background: #d32f2f;
  color: white;
}

.btn-danger:hover {
  background: #c62828;
}
</style>
