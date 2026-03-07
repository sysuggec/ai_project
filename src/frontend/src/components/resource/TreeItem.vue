<template>
  <div class="tree-item-wrapper">
    <div
      :class="['tree-item', { active: selected === item.path }]"
      :style="{ paddingLeft: (level * 20 + 8) + 'px' }"
      @click="handleClick"
    >
      <span
        v-if="hasChildren"
        class="toggle-icon"
        @click.stop="toggleExpand"
      >
        <svg :class="['arrow', { collapsed: !isExpanded }]" viewBox="0 0 24 24" width="16" height="16">
          <path fill="currentColor" d="M7 10l5 5 5-5z"/>
        </svg>
      </span>
      <span v-else class="toggle-placeholder"></span>
      <span class="folder-icon">{{ isExpanded && hasChildren ? '📂' : '📁' }}</span>
      <span class="name">{{ item.name }}</span>
      <span v-if="item.path === '/'" class="root-tag">根</span>
    </div>
    <div v-show="hasChildren && isExpanded" class="children-container">
      <TreeItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
        :selected="selected"
        :level="level + 1"
        @select="$emit('select', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
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

const emit = defineEmits(['select'])

const isExpanded = ref(true)

const hasChildren = computed(() => props.item.children && props.item.children.length > 0)

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

const handleClick = () => {
  // 选中当前目录
  emit('select', props.item.path)
  // 如果有子节点，同时切换折叠状态
  if (hasChildren.value) {
    toggleExpand()
  }
}
</script>

<style scoped>
.tree-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
}

.tree-item:hover {
  background: #f0f0f0;
}

.tree-item.active {
  background: #e6f7ff;
  color: #1890ff;
}

.toggle-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-radius: 4px;
  margin-right: 4px;
  color: #666;
}

.toggle-icon:hover {
  background: #e0e0e0;
}

.toggle-icon .arrow {
  transition: transform 0.2s ease;
}

.toggle-icon .arrow.collapsed {
  transform: rotate(-90deg);
}

.toggle-placeholder {
  width: 20px;
  height: 20px;
  margin-right: 4px;
}

.folder-icon {
  margin-right: 8px;
  font-size: 16px;
}

.name {
  font-size: 14px;
  flex: 1;
}

.root-tag {
  margin-left: 8px;
  padding: 2px 6px;
  background: #1890ff;
  color: white;
  font-size: 10px;
  border-radius: 4px;
}

.children-container {
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
