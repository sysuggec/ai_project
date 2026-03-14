<template>
  <div class="pagination" v-if="totalPages > 1">
    <button
      class="pagination-btn"
      :disabled="currentPage === 1"
      @click="$emit('page-change', currentPage - 1)"
    >
      上一页
    </button>

    <div class="pagination-pages">
      <button
        v-for="page in visiblePages"
        :key="page"
        :class="['pagination-page-btn', { active: page === currentPage }]"
        @click="$emit('page-change', page)"
      >
        {{ page }}
      </button>
    </div>

    <button
      class="pagination-btn"
      :disabled="currentPage === totalPages"
      @click="$emit('page-change', currentPage + 1)"
    >
      下一页
    </button>

    <div class="pagination-info">
      <select
        class="page-size-select"
        :value="perPage"
        @change="$emit('page-size-change', parseInt($event.target.value))"
      >
        <option value="10">10条/页</option>
        <option value="20">20条/页</option>
        <option value="50">50条/页</option>
        <option value="100">100条/页</option>
      </select>
      <span class="pagination-text">
        共 {{ total }} 条，第 {{ currentPage }} / {{ totalPages }} 页
      </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['page-change', 'page-size-change'])

const visiblePages = () => {
  const pages = []
  const delta = 2
  const range = []

  for (
    let i = Math.max(2, props.currentPage - delta);
    i <= Math.min(props.totalPages - 1, props.currentPage + delta);
    i++
  ) {
    range.push(i)
  }

  if (props.currentPage - delta > 2) {
    range.unshift('...')
  }

  if (props.currentPage + delta < props.totalPages - 1) {
    range.push('...')
  }

  if (props.totalPages >= 1) {
    range.unshift(1)
  }

  if (props.totalPages >= 2) {
    range.push(props.totalPages)
  }

  return range
}
</script>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px;
  background: #f5f5f5;
  border-radius: 8px;
}

.pagination-pages {
  display: flex;
  gap: 4px;
}

.pagination-btn,
.pagination-page-btn {
  padding: 8px 12px;
  border: 1px solid #ddd;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn:hover:not(:disabled),
.pagination-page-btn:hover:not(.active) {
  background: #f0f0f0;
}

.pagination-page-btn.active {
  background: #1976d2;
  color: #fff;
  border-color: #1976d2;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-size-select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: #fff;
  cursor: pointer;
  font-size: 14px;
}

.pagination-text {
  font-size: 14px;
  color: #666;
}

@media (max-width: 768px) {
  .pagination {
    flex-direction: column;
    gap: 12px;
  }

  .pagination-pages {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
