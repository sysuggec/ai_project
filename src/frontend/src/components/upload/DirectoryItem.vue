<template>
  <div class="directory-item-wrapper">
    <div
      :class="['directory-item', { active: selected === item.path }]"
      :style="{ paddingLeft: (level * 16 + 12) + 'px' }"
    >
      <span
        v-if="hasChildren"
        :class="['toggle-icon', { expanded: isExpanded }]"
        @click.stop="toggleExpand"
      >
        {{ isExpanded ? '▼' : '▶' }}
      </span>
      <span v-else class="toggle-placeholder"></span>
      <span class="folder-icon" @click="$emit('select', item.path)">📁</span>
      <span class="name" @click="$emit('select', item.path)">{{ item.name || '根目录' }}</span>
    </div>
    <template v-if="hasChildren && isExpanded">
      <DirectoryItem
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

defineEmits(['select'])

const isExpanded = ref(true)

const hasChildren = computed(() => props.item.children && props.item.children.length > 0)

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<style scoped>
.directory-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 6px;
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

.toggle-icon {
  width: 16px;
  font-size: 10px;
  color: #999;
  cursor: pointer;
  transition: transform 0.2s;
  user-select: none;
}

.toggle-placeholder {
  width: 16px;
}

.folder-icon {
  margin-right: 8px;
}

.name {
  font-size: 14px;
}
</style>
