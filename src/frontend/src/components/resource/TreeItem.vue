<template>
  <div class="tree-item-wrapper">
    <div
      :class="['tree-item', { active: selected === item.path }]"
      :style="{ paddingLeft: (level * 16 + 12) + 'px' }"
      @click="$emit('select', item.path)"
    >
      <span class="icon">📁</span>
      <span class="name">{{ item.name }}</span>
      <span v-if="item.path === '/'" class="root-tag">根</span>
    </div>
    <template v-if="item.children && item.children.length > 0">
      <TreeItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
        :selected="selected"
        :level="level + 1"
        @select="$emit('select', $event)"
      />
    </template>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  },
  selected: {
    type: String,
    default: '/'
  },
  level: {
    type: Number,
    default: 0
  }
})

defineEmits(['select'])
</script>

<style scoped>
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

.root-tag {
  margin-left: 8px;
  padding: 2px 6px;
  background: #1890ff;
  color: white;
  font-size: 10px;
  border-radius: 4px;
}
</style>
