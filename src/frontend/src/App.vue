<template>
  <div class="app">
    <header class="header">
      <h1>资源管理系统</h1>
      <nav class="tabs">
        <button
          :class="['tab', { active: activeTab === 'upload' }]"
          @click="activeTab = 'upload'"
        >
          上传资源
        </button>
        <button
          :class="['tab', { active: activeTab === 'resource' }]"
          @click="activeTab = 'resource'"
        >
          资源列表
        </button>
      </nav>
    </header>

    <main class="main">
      <UploadPage v-if="activeTab === 'upload'" />
      <ResourcePage v-else />
    </main>

    <Toast ref="toast" />
  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import UploadPage from './pages/UploadPage.vue'
import ResourcePage from './pages/ResourcePage.vue'
import Toast from './components/common/Toast.vue'

const activeTab = ref('upload')
const toast = ref(null)

provide('showToast', (message, type = 'info') => {
  toast.value?.show(message, type)
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f5f5f5;
  min-height: 100vh;
}

.app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: #fff;
  padding: 16px 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header h1 {
  font-size: 24px;
  color: #333;
  margin-bottom: 16px;
}

.tabs {
  display: flex;
  gap: 8px;
}

.tab {
  padding: 10px 24px;
  border: none;
  background: #f0f0f0;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
}

.tab.active {
  background: #1890ff;
  color: #fff;
}

.tab:hover:not(.active) {
  background: #e0e0e0;
}

.main {
  flex: 1;
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

@media (max-width: 768px) {
  .header {
    padding: 12px 16px;
  }

  .header h1 {
    font-size: 20px;
  }

  .main {
    padding: 16px;
  }
}
</style>
