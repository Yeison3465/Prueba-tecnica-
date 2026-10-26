<template>
  <div class="text-center py-5">
    <div class="mb-3 text-secondary opacity-50">
      <i :class="[icono || 'fa-regular fa-folder-open', 'fa-3x']"></i>
    </div>
    <h5 class="fw-bold text-dark">{{ titulo || 'No se encontraron resultados' }}</h5>
    <p class="text-muted small mb-3">{{ mensaje || 'No hay elementos para mostrar en este momento.' }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  icono: {
    type: String,
    default: 'fa-regular fa-folder-open',
  },
  titulo: {
    type: String,
    default: 'No se encontraron resultados',
  },
  mensaje: {
    type: String,
    default: '',
  },
});
</script>
