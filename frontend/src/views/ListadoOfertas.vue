<template>
  <div>
    <!-- Encabezado y Acciones -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <h1 class="h3 fw-bold text-dark mb-1">Listado de Ofertas Registradas</h1>
        <p class="text-muted mb-0">Gestión de convocatorias y licitaciones en línea (Vue.js SPA).</p>
      </div>
      <div class="d-flex gap-2">
        <button @click="exportarExcel" class="btn btn-excel d-flex align-items-center gap-2 shadow-sm">
          <i class="fa-solid fa-file-excel"></i>
          <span>Descargar Reporte Excel</span>
        </button>
        <router-link to="/ofertas/crear" class="btn btn-suplos d-flex align-items-center gap-2 shadow-sm">
          <i class="fa-solid fa-plus"></i>
          <span>Crear Licitación</span>
        </router-link>
      </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card card-custom mb-4">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">
            <i class="fa-solid fa-filter me-1"></i> Filtros de Búsqueda
          </span>
          <button v-if="filters.consecutivo || filters.objeto || filters.descripcion" 
                  @click="limpiarFiltros" 
                  class="btn btn-link btn-sm text-danger text-decoration-none p-0">
            <i class="fa-solid fa-rotate-left me-1"></i> Restablecer filtros
          </button>
        </div>
        
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label small text-muted">Consecutivo</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted border-end-0">
                <i class="fa-solid fa-hashtag"></i>
              </span>
              <input type="text" 
                     v-model="filters.consecutivo" 
                     class="form-control border-start-0" 
                     placeholder="Ej. PO-0001-26" />
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label small text-muted">Objeto</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted border-end-0">
                <i class="fa-solid fa-briefcase"></i>
              </span>
              <input type="text" 
                     v-model="filters.objeto" 
                     class="form-control border-start-0" 
                     placeholder="Buscar por objeto..." />
            </div>
          </div>

          <div class="col-md-5">
            <label class="form-label small text-muted">Descripción</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted border-end-0">
                <i class="fa-solid fa-align-left"></i>
              </span>
              <input type="text" 
                     v-model="filters.descripcion" 
                     class="form-control border-start-0" 
                     placeholder="Buscar por descripción..." />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de Ofertas -->
    <div class="card card-custom">
      <div class="card-body p-0">
        <!-- Spinner -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2 mb-0">Cargando ofertas con Axios...</p>
        </div>

        <!-- Listado -->
        <div v-else-if="ofertas.length > 0" class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
              <tr>
                <th class="ps-4">Consecutivo</th>
                <th>Objeto</th>
                <th>Descripción</th>
                <th class="text-center">Fecha Inicio</th>
                <th class="text-center">Fecha Cierre</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-4">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="oferta in ofertas" :key="oferta.id">
                <td class="ps-4">
                  <span class="badge-consecutivo">{{ oferta.consecutivo }}</span>
                </td>
                <td>
                  <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" :title="oferta.objeto">
                    {{ oferta.objeto }}
                  </div>
                  <div class="small text-muted" v-if="oferta.actividad">
                    <i class="fa-solid fa-tag me-1"></i>{{ oferta.actividad.producto }}
                  </div>
                </td>
                <td>
                  <div class="text-muted small text-truncate" style="max-width: 280px;" :title="oferta.descripcion">
                    {{ oferta.descripcion }}
                  </div>
                </td>
                <td class="text-center">
                  <div class="fw-semibold">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                  <div class="text-muted small">{{ oferta.hora_inicio }}</div>
                </td>
                <td class="text-center">
                  <div class="fw-semibold">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                  <div class="text-muted small">{{ oferta.hora_cierre }}</div>
                </td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2" 
                        v-if="oferta.estado === 'Publicada' || oferta.estado === 'Activo'">
                    {{ oferta.estado }}
                  </span>
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2" v-else>
                    {{ oferta.estado }}
                  </span>
                </td>
                <td class="text-center pe-4">
                  <div class="d-flex justify-content-center gap-1">
                    <router-link :to="'/ofertas/detalle/' + oferta.id" 
                                 class="btn btn-sm btn-outline-primary fw-semibold" 
                                 title="Ver Detalle">
                      <i class="fa-solid fa-eye me-1"></i> Ver Detalle
                    </router-link>
                    <router-link :to="'/ofertas/editar/' + oferta.id" 
                                 class="btn btn-sm btn-outline-secondary" 
                                 title="Editar Oferta">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sin resultados -->
        <div v-else class="text-center py-5">
          <i class="fa-regular fa-folder-open text-muted fa-3x mb-3"></i>
          <h5 class="fw-bold text-dark">No se encontraron ofertas</h5>
          <p class="text-muted mb-3">No hay registros que coincidan con la búsqueda.</p>
          <router-link to="/ofertas/crear" class="btn btn-suplos btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Crear la primera oferta
          </router-link>
        </div>

        <!-- Paginación -->
        <div v-if="pagination.total > 0" class="card-footer bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center py-3 px-4 gap-2">
          <div class="text-muted small">
            Mostrando del <b>{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</b>
            al <b>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</b>
            de <b>{{ pagination.total }}</b> registros
          </div>

          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <button class="page-link" @click="cargarOfertas(pagination.current_page - 1)">
                <i class="fa-solid fa-chevron-left"></i> Anterior
              </button>
            </li>
            <li class="page-item" 
                v-for="p in pagination.last_page" 
                :key="p" 
                :class="{ active: p === pagination.current_page }">
              <button class="page-link" @click="cargarOfertas(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <button class="page-link" @click="cargarOfertas(pagination.current_page + 1)">
                Siguiente <i class="fa-solid fa-chevron-right"></i>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../services/api';

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
  cargarOfertas(1);
};

const exportarExcel = () => {
  const params = new URLSearchParams(filters.value);
  window.location.href = `/ofertas/exportar-excel?${params.toString()}`;
};

const formatearFecha = (str) => {
  if (!str) return '';
  return str.split('T')[0];
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
