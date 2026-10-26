<template>
  <nav v-if="pagination && pagination.total > 0" class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 py-2">
    <div class="text-muted small">
      Mostrando <b>{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</b>
      a <b>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</b>
      de <b>{{ pagination.total }}</b> registros
    </div>

    <ul class="pagination pagination-sm mb-0">
      <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
        <button type="button" class="page-link" @click="$emit('cambiar-pagina', pagination.current_page - 1)">
          &laquo;
        </button>
      </li>
      <li
        class="page-item"
        v-for="p in pagination.last_page"
        :key="p"
        :class="{ active: p === pagination.current_page }"
      >
        <button type="button" class="page-link" @click="$emit('cambiar-pagina', p)">
          {{ p }}
        </button>
      </li>
      <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
        <button type="button" class="page-link" @click="$emit('cambiar-pagina', pagination.current_page + 1)">
          &raquo;
        </button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
defineProps({
  pagination: {
    type: Object,
    required: true,
  },
});

defineEmits(['cambiar-pagina']);
</script>
