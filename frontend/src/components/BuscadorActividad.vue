<template>
  <div class="position-relative w-100" ref="wrapperRef">
    <!-- 1. Vista cuando hay una actividad seleccionada -->
    <div v-if="actividadSeleccionada" class="alert alert-light border border-primary-subtle d-flex justify-content-between align-items-center mb-0 p-3 shadow-sm rounded">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-primary">UNSPSC {{ actividadSeleccionada.codigo_producto }}</span>
          <span v-if="actividadSeleccionada.codigo_clase" class="badge bg-secondary">Clase {{ actividadSeleccionada.codigo_clase }}</span>
        </div>
        <div class="fw-bold text-dark">{{ actividadSeleccionada.producto }}</div>
        <div class="text-muted small">
          {{ actividadSeleccionada.segmento || actividadSeleccionada.Segmento }}
          <span v-if="actividadSeleccionada.familia"> &rsaquo; {{ actividadSeleccionada.familia }}</span>
          <span v-if="actividadSeleccionada.clase"> &rsaquo; {{ actividadSeleccionada.clase }}</span>
        </div>
      </div>

      <div v-if="!disabled" class="d-flex gap-1">
        <button type="button" class="btn btn-sm btn-outline-primary" @click="abrirParaCambiar" title="Cambiar actividad">
          <i class="fa-solid fa-arrows-rotate me-1"></i> Cambiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" @click="quitarSeleccion" title="Quitar selección">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <!-- 2. Vista de búsqueda con input y dropdown -->
    <div v-else>
      <div class="input-group">
        <span class="input-group-text bg-white text-secondary">
          <i v-if="loading" class="fa-solid fa-circle-notch fa-spin"></i>
          <i v-else class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input
          ref="inputRef"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': invalid }"
          :placeholder="placeholder || 'Buscar por nombre, código UNSPSC o segmento...'"
          v-model="termino"
          @focus="onFocus"
          @blur="onBlur"
          @input="onInput"
          @keydown.esc="mostrarDropdown = false"
          :disabled="disabled"
          autocomplete="off"
        />
        <button v-if="termino" type="button" class="btn btn-outline-secondary" @click="limpiarTermino">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Menú desplegable con resultados Bootstrap estándar -->
      <div v-if="mostrarDropdown" class="dropdown-menu show w-100 shadow-sm p-0 overflow-auto mt-1" style="max-height: 260px; z-index: 1050;">
        <div class="dropdown-header d-flex justify-content-between align-items-center bg-light py-2 px-3 border-bottom">
          <span class="small fw-semibold text-secondary">
            {{ loading ? 'Buscando...' : `Resultados (${resultados.length})` }}
          </span>
          <span class="badge bg-secondary-subtle text-secondary small">UNSPSC</span>
        </div>

        <div v-if="loading" class="text-center py-3 text-muted small">
          <div class="spinner-border spinner-border-sm me-2" role="status"></div> Buscando actividades...
        </div>

        <div v-else-if="resultados.length === 0" class="text-center py-3 text-muted small px-3">
          No se encontraron actividades para "<b>{{ termino }}</b>"
        </div>

        <div v-else class="list-group list-group-flush">
          <button
            type="button"
            v-for="act in resultados"
            :key="act.id"
            class="list-group-item list-group-item-action py-2 px-3 border-bottom"
            @mousedown.prevent="seleccionar(act)"
          >
            <div class="d-flex justify-content-between align-items-center gap-2">
              <span class="fw-semibold text-dark text-truncate">{{ act.producto }}</span>
              <span class="badge bg-primary-subtle text-primary border small">UNSPSC {{ act.codigo_producto }}</span>
            </div>
            <div class="text-muted small text-truncate">
              {{ act.segmento || act.Segmento }}
              <span v-if="act.familia"> &bull; {{ act.familia }}</span>
              <span v-if="act.clase"> &bull; {{ act.clase }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useActividadSearch } from '../composables/useActividadSearch';

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: '',
  },
  actividadInicial: {
    type: Object,
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Buscar actividad por nombre, segmento o código UNSPSC...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  invalid: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'select']);

const wrapperRef = ref(null);
const inputRef = ref(null);
const mostrarDropdown = ref(false);

const {
  termino,
  resultados,
  loading,
  actividadSeleccionada,
  buscarActividades,
  debouncedBuscar,
  cargarPorId,
  limpiar,
} = useActividadSearch();

const onInput = () => {
  mostrarDropdown.value = true;
  debouncedBuscar(250);
};

const onFocus = () => {
  if (resultados.value.length === 0) {
    buscarActividades(termino.value);
  }
  mostrarDropdown.value = true;
};

const onBlur = () => {
  setTimeout(() => {
    mostrarDropdown.value = false;
  }, 200);
};

const limpiarTermino = () => {
  termino.value = '';
  buscarActividades('');
  if (inputRef.value) inputRef.value.focus();
};

const seleccionar = (act) => {
  actividadSeleccionada.value = act;
  mostrarDropdown.value = false;
  emit('update:modelValue', act.id);
  emit('select', act);
};

const abrirParaCambiar = () => {
  limpiar();
  emit('update:modelValue', '');
  nextTick(() => {
    if (inputRef.value) inputRef.value.focus();
    buscarActividades('');
    mostrarDropdown.value = true;
  });
};

const quitarSeleccion = () => {
  limpiar();
  emit('update:modelValue', '');
  mostrarDropdown.value = false;
};

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    cargarPorId(newVal, props.actividadInicial);
  } else {
    actividadSeleccionada.value = null;
  }
}, { immediate: true });

watch(() => props.actividadInicial, (newAct) => {
  if (newAct && (!actividadSeleccionada.value || actividadSeleccionada.value.id !== newAct.id)) {
    actividadSeleccionada.value = newAct;
  }
});

const handleClickOutside = (e) => {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    mostrarDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
