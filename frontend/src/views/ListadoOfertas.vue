<template>
  <div>
    <!-- Encabezado y Acciones Principales -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <h1 class="h4 fw-bold text-dark mb-1">Listado de Ofertas y Licitaciones</h1>
        <p class="text-muted mb-0 small">Consulte, filtre en tiempo real y gestione todas las ofertas registradas.</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button @click="exportarExcel" class="btn btn-outline-success d-flex align-items-center gap-2" title="Descargar consolidado en Excel">
          <i class="fa-solid fa-file-excel"></i>
          <span>Descargar Excel</span>
        </button>
        <router-link to="/ofertas/crear" class="btn btn-primary d-flex align-items-center gap-2">
          <i class="fa-solid fa-plus"></i>
          <span>Nueva Oferta</span>
        </router-link>
      </div>
    </div>

    <!-- Barra de Filtros Bootstrap -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
            <i class="fa-solid fa-filter me-1"></i> Filtros de Búsqueda
          </h6>

          <button v-if="filters.consecutivo || filters.objeto || filters.descripcion || filters.actividad" 
                  @click="limpiarFiltros" 
                  class="btn btn-sm btn-link text-danger text-decoration-none p-0">
            <i class="fa-solid fa-rotate-left me-1"></i> Limpiar filtros
          </button>
        </div>
        
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Consecutivo</label>
            <input type="text" 
                   v-model="filters.consecutivo" 
                   class="form-control form-control-sm" 
                   placeholder="Ej. PO-0001-26" />
          </div>

          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Objeto</label>
            <input type="text" 
                   v-model="filters.objeto" 
                   class="form-control form-control-sm" 
                   placeholder="Palabra clave..." />
          </div>

          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Actividad UNSPSC</label>
            <input type="text" 
                   v-model="filters.actividad" 
                   class="form-control form-control-sm" 
                   placeholder="Producto o código..." />
          </div>

          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Descripción</label>
            <input type="text" 
                   v-model="filters.descripcion" 
                   class="form-control form-control-sm" 
                   placeholder="Alcance..." />
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla Principal con Bootstrap Estándar -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <!-- Spinner -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2 mb-0 small">Cargando ofertas...</p>
        </div>

        <!-- Listado -->
        <div v-else-if="ofertas.length > 0" class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Consecutivo</th>
                <th>Objeto / Actividad</th>
                <th>Descripción</th>
                <th class="text-center">Inicio</th>
                <th class="text-center">Cierre</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-3">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="oferta in ofertas" :key="oferta.id">
                <td class="ps-3 fw-bold text-dark">
                  {{ oferta.consecutivo }}
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 240px;" :title="oferta.objeto">
                    {{ oferta.objeto }}
                  </div>
                  <div class="small text-muted text-truncate" style="max-width: 240px;" v-if="oferta.actividad" :title="oferta.actividad.producto">
                    <i class="fa-solid fa-tag me-1 text-primary small"></i>{{ oferta.actividad.producto }}
                  </div>
                </td>
                <td>
                  <div class="text-muted small text-truncate" style="max-width: 260px;" :title="oferta.descripcion">
                    {{ oferta.descripcion }}
                  </div>
                </td>
                <td class="text-center">
                  <div class="small text-dark">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ oferta.hora_inicio }}</div>
                </td>
                <td class="text-center">
                  <div class="small text-dark">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ oferta.hora_cierre }}</div>
                </td>
                <td class="text-center">
                  <EstadoBadge :estado="obtenerEstado(oferta)" />
                </td>
                <td class="text-center pe-3">
                  <div class="d-flex justify-content-center gap-1">
                    <router-link :to="'/ofertas/detalle/' + oferta.id" 
                                 class="btn btn-sm btn-outline-primary px-2 py-1" 
                                 title="Ver Detalle Completo">
                      <i class="fa-solid fa-eye me-1"></i> Detalle
                    </router-link>
                    <router-link :to="'/ofertas/editar/' + oferta.id" 
                                 class="btn btn-sm btn-outline-secondary px-2 py-1" 
                                 title="Editar Oferta">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sin resultados (Componente Reutilizable) -->
        <EmptyState 
          v-else 
          titulo="No se encontraron ofertas" 
          mensaje="No hay registros que coincidan con los criterios de búsqueda.">
          <router-link to="/ofertas/crear" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Crear la primera oferta
          </router-link>
        </EmptyState>

        <!-- Paginación Reutilizable -->
        <div v-if="pagination.total > 0" class="card-footer bg-white border-top px-3 py-2">
          <AppPaginacion :pagination="pagination" @cambiar-pagina="cargarOfertas" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../services/api';
import { formatearFecha } from '../utils/formatters';
import { useOfertaEstado } from '../composables/useOfertaEstado';
import EstadoBadge from '../components/EstadoBadge.vue';
import AppPaginacion from '../components/AppPaginacion.vue';
import EmptyState from '../components/EmptyState.vue';

const { obtenerEstado } = useOfertaEstado(3000);

const ofertas = ref([]);
const loading = ref(false);
const pagination = ref({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
});

const filters = ref({
  consecutivo: '',
  objeto: '',
  descripcion: '',
  actividad: '',
});

let debounceTimer = null;

const cargarOfertas = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get('/ofertas', {
      params: {
        page,
        per_page: pagination.value.per_page,
        consecutivo: filters.value.consecutivo,
        objeto: filters.value.objeto,
        descripcion: filters.value.descripcion,
        actividad: filters.value.actividad,
      },
    });
    if (res.data.success) {
      ofertas.value = res.data.data;
      pagination.value = res.data.pagination;
    }
  } catch (err) {
    console.error('Error al cargar ofertas:', err);
  } finally {
    loading.value = false;
  }
};

const limpiarFiltros = () => {
  filters.value.consecutivo = '';
  filters.value.objeto = '';
  filters.value.descripcion = '';
  filters.value.actividad = '';
  cargarOfertas(1);
};

const exportarExcel = () => {
  const params = new URLSearchParams(filters.value);
  window.location.href = `/ofertas/exportar-excel?${params.toString()}`;
};

watch(filters, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    cargarOfertas(1);
  }, 300);
}, { deep: true });

onMounted(() => {
  cargarOfertas(1);
});
</script>
