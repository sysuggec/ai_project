<template>
  <div class="directory-picker">
    <h3 class="title">保存目录</h3>
    <div class="current-path">
      <span class="path-label">当前目录：</span>
      <span class="path-value">{{ modelValue }}</span>
    </div>
    <div class="directory-list">
      <DirectoryItem
        v-for="dir in directories"
        :key="dir.id"
        :item="dir"
        :selected="modelValue"
        :level="0"
        @select="$emit('update:modelValue', $event)"
      />
    </div>
    <div class="new-directory">
      <input
        v-model="newDirectoryName"
        type="text"
        placeholder="新建子目录名"
        class="input"
      />
      <button class="btn-create" @click="createDirectory">创建</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { getDirectories, createDirectory as createDir } from '../../api/resource'
import DirectoryItem from './DirectoryItem.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '/'
  }
})

const emit = defineEmits(['update:modelValue'])

const directories = ref([])
const newDirectoryName = ref('')
const showToast = inject('showToast')

onMounted(async () => {
  await loadDirectories()
})

const loadDirectories = async () => {
  try {
    const result = await getDirectories()
    directories.value = result?.directories ?? []
  } catch (error) {
    console.error('加载目录失败', error)
    directories.value = []
  }
}

const createDirectory = async () => {
  if (!newDirectoryName.value.trim()) {
    showToast('请输入目录名', 'error')
    return
  }

  const newPath = props.modelValue === '/'
    ? '/' + newDirectoryName.value
    : props.modelValue + '/' + newDirectoryName.value

  try {
    await createDir(newPath)
    await loadDirectories()
    emit('update:modelValue', newPath)
    newDirectoryName.value = ''
    showToast('目录创建成功', 'success')
  } catch (error) {
    showToast('创建目录失败: ' + error.message, 'error')
  }
}
</script>

<style scoped>
.directory-picker {
  background: #fff;
}

.title {
  font-size: 16px;
  color: #333;
  margin-bottom: 12px;
}

.current-path {
  padding: 12px;
  background: #f5f5f5;
  border-radius: 8px;
  margin-bottom: 12px;
}

.path-label {
  color: #666;
}

.path-value {
  color: #1890ff;
  font-weight: 500;
}

.directory-list {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 12px;
}

.directory-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.directory-item:hover {
  background: #f5f5f5;
}

.directory-item.active {
  background: #e6f7ff;
  color: #1890ff;
}

.dir-icon {
  margin-right: 8px;
}

.dir-name {
  font-size: 14px;
}

.new-directory {
  display: flex;
  gap: 8px;
}

.input {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  font-size: 14px;
}

.input:focus {
  outline: none;
  border-color: #1890ff;
}

.btn-create {
  padding: 10px 16px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-create:hover {
  background: #40a9ff;
}
</style>
