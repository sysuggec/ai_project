<template>
  <div class="directory-tree">
    <h3 class="title">目录</h3>
    <div class="tree">
      <div
        :class="['tree-item', { active: selected === '/' }]"
        @click="$emit('select', '/')"
      >
        <span class="icon">📁</span>
        <span class="name">根目录</span>
      </div>
      <template v-for="dir in directories" :key="dir.id">
        <TreeItem
          :item="dir"
          :selected="selected"
          @select="$emit('select', $event)"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import TreeItem from './TreeItem.vue'

defineProps({
  directories: {
    type: Array,
    default: () => []
  },
  selected: {
    type: String,
    default: '/'
  }
})

defineEmits(['select'])
</script>

<style scoped>
.directory-tree {
  height: 100%;
}

.title {
  font-size: 16px;
  color: #333;
  margin-bottom: 16px;
}

.tree {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.tree-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.tree-item:hover {
  background: #f0f0f0;
}

.tree-item.active {
  background: #e6f7ff;
  color: #1890ff;
}

.icon {
  margin-right: 8px;
}

.name {
  font-size: 14px;
}
</style>
