<template>
  <div class="buscador-actividad-wrapper" ref="wrapperRef">
    <!-- 1. VISTA CUANDO HAY UNA ACTIVIDAD SELECCIONADA -->
    <div v-if="actividadSeleccionada" class="actividad-seleccionada-card p-3 rounded-3 border">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <div class="d-flex align-items-start gap-2">
          <div class="actividad-icon-box mt-1">
            <i class="fa-solid fa-tag text-cyan"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <span class="badge-unspsc">UNSPSC: {{ actividadSeleccionada.codigo_producto }}</span>
              <span class="badge bg-light text-muted border small" v-if="actividadSeleccionada.clase">
                Clase {{ actividadSeleccionada.codigo_clase }}
              </span>
            </div>
            <div class="fw-bold text-navy fs-6 mb-1">
              {{ actividadSeleccionada.producto }}
            </div>
            <div class="small text-muted text-truncate" style="max-width: 380px;" :title="actividadSeleccionada.segmento">
              <span class="fw-semibold">Segmento:</span> {{ actividadSeleccionada.segmento || actividadSeleccionada.Segmento }}
              <span v-if="actividadSeleccionada.familia"> &bull; <span class="fw-semibold">Familia:</span> {{ actividadSeleccionada.familia }}</span>
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-1" v-if="!disabled">
          <button type="button" 
                  class="btn btn-sm btn-outline-minimal px-2 py-1" 
                  @click="abrirBuscadorParaCambiar" 
                  title="Cambiar actividad seleccionada">
            <i class="fa-solid fa-arrows-rotate text-cyan me-1 small"></i>
            <span class="small">Cambiar</span>
          </button>
          <button type="button" 
                  class="btn btn-sm btn-outline-danger px-2 py-1" 
                  @click="limpiarSeleccion" 
                  title="Quitar actividad">
            <i class="fa-solid fa-xmark small"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- 2. VISTA CUANDO SE ESTÁ BUSCANDO / NO HAY SELECCIÓN -->
    <div v-else class="position-relative">
      <div class="input-group">
        <span class="input-group-text bg-white text-muted border-end-0">
          <i v-if="loading" class="fa-solid fa-circle-notch fa-spin text-cyan"></i>
          <i v-else class="fa-solid fa-magnifying-glass text-muted small"></i>
        </span>
        <input 
          ref="inputBusquedaRef"
          type="text" 
          class="form-control border-start-0" 
          :class="{ 'is-invalid': invalid }"
          :placeholder="placeholder || 'Escriba nombre, código UNSPSC o segmento...'" 
          v-model="termino"
          @focus="onFocus"
          @input="onInput"
          @keydown.esc="cerrarDropdown"
          :disabled="disabled"
          autocomplete="off"
        />
        <button v-if="termino" 
                type="button" 
                class="btn btn-outline-secondary border-start-0 bg-white" 
                @click="limpiarTermino">
          <i class="fa-solid fa-xmark text-muted small"></i>
        </button>
      </div>

      <!-- Menú desplegable con los resultados de la búsqueda -->
      <div v-if="mostrarDropdown" class="dropdown-menu show w-100 shadow-lg border mt-1 p-0 resultados-dropdown">
        <!-- Encabezado del dropdown -->
        <div class="dropdown-header-custom py-2 px-3 border-bottom bg-light d-flex justify-content-between align-items-center">
          <span class="small text-muted fw-semibold" style="font-size: 0.725rem;">
            {{ loading ? 'Buscando en catálogo...' : `Resultados (${resultados.length})` }}
          </span>
          <span class="badge bg-white text-navy border small" style="font-size: 0.7rem;">
            Catálogo UNSPSC
          </span>
        </div>

        <!-- Lista scrolleable de resultados -->
        <div class="resultados-scroll">
          <!-- Spinner dentro de lista -->
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-cyan me-2" role="status"></div>
            <span class="small text-muted">Buscando actividades en la base de datos...</span>
          </div>

          <!-- Sin resultados -->
          <div v-else-if="resultados.length === 0" class="text-center py-4 px-3">
            <i class="fa-solid fa-folder-open text-muted opacity-50 mb-2 fa-lg"></i>
            <div class="small fw-semibold text-dark">No se encontraron actividades</div>
            <div class="text-muted" style="font-size: 0.75rem;">
              No hay coincidencias para "<span class="fw-bold">{{ termino }}</span>". Intente con otra palabra clave o código.
            </div>
          </div>

          <!-- Items encontrados -->
          <div v-else>
            <button 
              type="button" 
              v-for="act in resultados" 
              :key="act.id" 
              class="dropdown-item py-2 px-3 border-bottom item-actividad text-wrap" 
              @click="seleccionar(act)"
            >
              <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <span class="fw-bold text-navy item-producto">{{ act.producto }}</span>
                <span class="badge-unspsc-small">UNSPSC {{ act.codigo_producto }}</span>
              </div>
              <div class="small text-muted item-jerarquia">
                <span>{{ act.segmento || act.Segmento }}</span>
                <span v-if="act.familia"> &bull; {{ act.familia }}</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Pie del dropdown -->
        <div class="py-2 px-3 bg-light border-top text-muted d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
          <span><i class="fa-solid fa-keyboard me-1 text-cyan"></i>Escriba para filtrar en tiempo real</span>
          <span class="text-secondary">ESC para cerrar</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import api from '../services/api';

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
const inputBusquedaRef = ref(null);
const termino = ref('');
const resultados = ref([]);
const loading = ref(false);
const mostrarDropdown = ref(false);
const actividadSeleccionada = ref(null);

let debounceTimer = null;

// Buscar actividades en el backend vía Axios
const buscarActividades = async (query = '') => {
  loading.value = true;
  try {
    const res = await api.get('/actividades', {
      params: { search: query.trim() },
    });
    if (res.data.success) {
      resultados.value = res.data.data;
    }
  } catch (err) {
    console.error('Error al buscar actividades:', err);
  } finally {
    loading.value = false;
  }
};

const onInput = () => {
  mostrarDropdown.value = true;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    buscarActividades(termino.value);
  }, 250);
};

const onFocus = () => {
  if (resultados.value.length === 0) {
    buscarActividades(termino.value);
  }
  mostrarDropdown.value = true;
};

const cerrarDropdown = () => {
  mostrarDropdown.value = false;
};

const limpiarTermino = () => {
  termino.value = '';
  buscarActividades('');
  if (inputBusquedaRef.value) {
    inputBusquedaRef.value.focus();
  }
};

const seleccionar = (act) => {
  actividadSeleccionada.value = act;
  mostrarDropdown.value = false;
  emit('update:modelValue', act.id);
  emit('select', act);
};

const abrirBuscadorParaCambiar = () => {
  actividadSeleccionada.value = null;
  termino.value = '';
  emit('update:modelValue', '');
  nextTick(() => {
    if (inputBusquedaRef.value) {
      inputBusquedaRef.value.focus();
    }
    buscarActividades('');
    mostrarDropdown.value = true;
  });
};

const limpiarSeleccion = () => {
  actividadSeleccionada.value = null;
  termino.value = '';
  emit('update:modelValue', '');
  mostrarDropdown.value = false;
};

// Cargar información de la actividad seleccionada cuando cambia modelValue externamente
const cargarActividadPorId = async (id) => {
  if (!id) {
    actividadSeleccionada.value = null;
    return;
  }

  // Si ya coincide con la seleccionada actualmente, no consultar de nuevo
  if (actividadSeleccionada.value && actividadSeleccionada.value.id === parseInt(id)) {
    return;
  }

  // Si nos pasaron el objeto inicial
  if (props.actividadInicial && props.actividadInicial.id === parseInt(id)) {
    actividadSeleccionada.value = props.actividadInicial;
    return;
  }

  try {
    const res = await api.get(`/actividades/${id}`);
    if (res.data.success) {
      actividadSeleccionada.value = res.data.data;
    }
  } catch (err) {
    console.error('Error al obtener detalle de actividad:', err);
  }
};

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    cargarActividadPorId(newVal);
  } else {
    actividadSeleccionada.value = null;
  }
}, { immediate: true });

watch(() => props.actividadInicial, (newAct) => {
  if (newAct && (!actividadSeleccionada.value || actividadSeleccionada.value.id !== newAct.id)) {
    actividadSeleccionada.value = newAct;
  }
});

// Manejo de clic por fuera para cerrar el menú
const handleClickOutside = (event) => {
  if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
    cerrarDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (debounceTimer) clearTimeout(debounceTimer);
});
</script>

<style scoped>
.buscador-actividad-wrapper {
  position: relative;
  width: 100%;
}

.actividad-seleccionada-card {
  background-color: #f8fafc;
  border-color: #cbd5e1 !important;
  transition: all 0.2s ease;
}

.actividad-seleccionada-card:hover {
  background-color: #f1f5f9;
  border-color: #94a3b8 !important;
}

.actividad-icon-box {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background-color: #e0f2fe;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.badge-unspsc {
  background-color: #0b2853;
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  letter-spacing: 0.5px;
}

.badge-unspsc-small {
  background-color: #e0f2fe;
  color: #0369a1;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  white-space: nowrap;
}

.resultados-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1050;
  border-radius: 8px;
  background-color: #ffffff;
  overflow: hidden;
}

.resultados-scroll {
  max-height: 280px;
  overflow-y: auto;
}

.item-actividad {
  cursor: pointer;
  transition: background-color 0.15s ease;
  white-space: normal;
}

.item-actividad:hover {
  background-color: #f0f9ff;
}

.item-producto {
  font-size: 0.85rem;
  line-height: 1.3;
}

.item-jerarquia {
  font-size: 0.725rem;
}
</style>
