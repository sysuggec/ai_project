<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="cancel">
      <div class="modal">
        <h3 class="modal-title">重命名</h3>
        <input
          v-model="newName"
          type="text"
          class="input"
          placeholder="请输入新文件名"
          @keyup.enter="confirm"
        />
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="cancel">取消</button>
          <button class="btn btn-primary" @click="confirm">确认</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const newName = ref('')
let resolvePromise = null

const show = (currentName) => {
  newName.value = currentName
  visible.value = true
  return new Promise((resolve) => {
    resolvePromise = resolve
  })
}

const confirm = () => {
  visible.value = false
  resolvePromise?.(newName.value.trim())
}

const cancel = () => {
  visible.value = false
  resolvePromise?.(null)
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
  margin-bottom: 16px;
}

.input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  font-size: 14px;
  margin-bottom: 24px;
}

.input:focus {
  outline: none;
  border-color: #1890ff;
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
