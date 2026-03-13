<template>
  <Transition name="modal">
    <div v-if="isVisible" class="modal-overlay" @click.self="handleCancel">
      <div class="modal">
        <h3 class="modal-title">{{ currentTitle }}</h3>
        <p class="modal-message">{{ currentMessage }}</p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="handleCancel">取消</button>
          <button class="btn btn-primary" @click="handleConfirm">确认</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['confirm', 'cancel'])

// 内部状态（用于 ref.show() 方式）
const internalVisible = ref(false)
const internalTitle = ref('')
const internalMessage = ref('')
let resolvePromise = null

// 计算实际显示状态和内容
// 优先使用内部状态（ref.show() 方式），否则使用 props
const isVisible = computed(() => {
  return internalVisible.value || props.visible
})

const currentTitle = computed(() => {
  return internalTitle.value || props.title
})

const currentMessage = computed(() => {
  return internalMessage.value || props.message
})

// ref.show() 方式的接口
const show = (title, message) => {
  internalTitle.value = title
  internalMessage.value = message
  internalVisible.value = true
  return new Promise((resolve) => {
    resolvePromise = resolve
  })
}

const handleConfirm = () => {
  // 如果是 ref.show() 方式调用
  if (resolvePromise) {
    internalVisible.value = false
    resolvePromise(true)
    resolvePromise = null
  }
  // 同时 emit 事件（支持 props + events 方式）
  emit('confirm')
}

const handleCancel = () => {
  // 如果是 ref.show() 方式调用
  if (resolvePromise) {
    internalVisible.value = false
    resolvePromise(false)
    resolvePromise = null
  }
  // 同时 emit 事件（支持 props + events 方式）
  emit('cancel')
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
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
}

.modal-title {
  font-size: 18px;
  color: #333;
  margin-bottom: 12px;
}

.modal-message {
  font-size: 14px;
  color: #666;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn {
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #1890ff;
  color: #fff;
}

.btn-primary:hover {
  background: #40a9ff;
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
  transform: scale(0.9);
}
</style>
