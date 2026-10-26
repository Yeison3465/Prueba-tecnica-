<template>
  <span class="badge" :class="badgeClass">
    {{ estado || 'Sin estado' }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  estado: {
    type: String,
    default: '',
  },
});

const badgeClass = computed(() => {
  switch (props.estado) {
    case 'Publicada':
    case 'Activo':
      return 'bg-success';
    case 'Cerrada':
      return 'bg-danger';
    case 'Borrador':
      return 'bg-warning text-dark';
    default:
      return 'bg-secondary';
  }
});
</script>
