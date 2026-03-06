<template>
  <Transition name="toast">
    <div v-if="visible" :class="['toast', type]">
      {{ message }}
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('info')
let timer = null

const show = (msg, t = 'info') => {
  message.value = msg
  type.value = t
  visible.value = true

  if (timer) {
    clearTimeout(timer)
  }

  timer = setTimeout(() => {
    visible.value = false
  }, 3000)
}

defineExpose({ show })
</script>

<style scoped>
.toast {
  position: fixed;
  top: 24px;
  right: 24px;
  padding: 12px 24px;
  border-radius: 8px;
  color: #fff;
  font-size: 14px;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast.info {
  background: #1890ff;
}

.toast.success {
  background: #52c41a;
}

.toast.error {
  background: #ff4d4f;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
