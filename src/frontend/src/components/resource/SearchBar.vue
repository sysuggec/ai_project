<template>
  <div class="search-bar">
    <input
      :value="modelValue"
      type="text"
      placeholder="搜索文件名..."
      class="search-input"
      @input="$emit('update:modelValue', $event.target.value)"
      @keyup.enter="$emit('search')"
    />
    <button class="search-btn" @click="$emit('search')">搜索</button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue', 'search'])
</script>

<style scoped>
.search-bar {
  display: flex;
  gap: 8px;
  flex: 1;
}

.search-input {
  flex: 1;
  padding: 10px 16px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #1890ff;
}

.search-btn {
  padding: 10px 20px;
  background: #1890ff;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.search-btn:hover {
  background: #40a9ff;
}
</style>
