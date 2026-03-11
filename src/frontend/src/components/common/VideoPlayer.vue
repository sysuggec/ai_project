<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="close">
      <div class="player-container">
        <button class="close-btn" @click="close">×</button>
        <div class="video-wrapper">
          <video
            ref="videoRef"
            :src="videoUrl"
            controls
            autoplay
            @loadeddata="onLoad"
            @error="onError"
          ></video>
          <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
          </div>
        </div>
        <div class="file-name">{{ fileName }}</div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const videoUrl = ref('')
const fileName = ref('')
const loading = ref(false)
const videoRef = ref(null)

const show = (url, name) => {
  videoUrl.value = url
  fileName.value = name
  visible.value = true
  loading.value = true
}

const close = () => {
  visible.value = false
  // 暂停视频
  if (videoRef.value) {
    videoRef.value.pause()
  }
}

const onLoad = () => {
  loading.value = false
}

const onError = () => {
  loading.value = false
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

.player-container {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.close-btn {
  position: absolute;
  top: -40px;
  right: 0;
  width: 36px;
  height: 36px;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  font-size: 28px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.video-wrapper {
  position: relative;
  max-width: 100%;
  max-height: calc(90vh - 60px);
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
}

.video-wrapper video {
  max-width: 90vw;
  max-height: calc(90vh - 60px);
  outline: none;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 8px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.file-name {
  margin-top: 16px;
  color: #fff;
  font-size: 14px;
  text-align: center;
  max-width: 80vw;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .player-container,
.modal-leave-to .player-container {
  transform: scale(0.9);
}
</style>
