<template>
  <div>
    <!-- Encabezado y Acciones Principales -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-white text-muted border px-2 py-1 small fw-medium">Convocatorias</span>
          <span class="text-muted small">&bull;</span>
          <span class="text-muted small">Módulo Corporativo</span>
        </div>
        <h1 class="h4 fw-bold text-navy mb-1">Listado de Ofertas y Licitaciones</h1>
        <p class="text-muted mb-0 small">Consulte, filtre en tiempo real y gestione todas las ofertas registradas.</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button @click="exportarExcel" class="btn btn-excel d-flex align-items-center gap-2 shadow-none" title="Descargar consolidado completo">
          <i class="fa-solid fa-file-excel small"></i>
          <span>Descargar Excel</span>
        </button>
        <router-link to="/ofertas/crear" class="btn btn-suplos-cyan d-flex align-items-center gap-2 shadow-none">
          <i class="fa-solid fa-plus small"></i>
          <span>Crear Licitación</span>
        </router-link>
      </div>
    </div>

    <!-- Barra de Filtros Minimalista -->
    <div class="card card-custom mb-4">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="section-icon" style="width: 24px; height: 24px; font-size: 0.75rem;">
              <i class="fa-solid fa-filter"></i>
            </span>
            <span class="text-uppercase small fw-bold text-navy" style="letter-spacing: 0.5px; font-size: 0.75rem;">
              Filtros de Búsqueda Reactivos
            </span>
          </div>

          <button v-if="filters.consecutivo || filters.objeto || filters.descripcion" 
                  @click="limpiarFiltros" 
                  class="btn btn-link btn-sm text-danger text-decoration-none p-0 small fw-semibold">
            <i class="fa-solid fa-rotate-left me-1"></i> Limpiar filtros
          </button>
        </div>
        
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Consecutivo</label>
            <div class="input-group">
              <span class="input-group-text bg-white text-muted border-end-0 small">
                <i class="fa-solid fa-hashtag small"></i>
              </span>
              <input type="text" 
                     v-model="filters.consecutivo" 
                     class="form-control border-start-0" 
                     placeholder="Ej. PO-0001-26" />
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Objeto de la Oferta</label>
            <div class="input-group">
              <span class="input-group-text bg-white text-muted border-end-0 small">
                <i class="fa-solid fa-briefcase small"></i>
              </span>
              <input type="text" 
                     v-model="filters.objeto" 
                     class="form-control border-start-0" 
                     placeholder="Filtrar por palabra clave..." />
            </div>
          </div>

          <div class="col-md-5">
            <label class="form-label">Descripción / Alcance</label>
            <div class="input-group">
              <span class="input-group-text bg-white text-muted border-end-0 small">
                <i class="fa-solid fa-align-left small"></i>
              </span>
              <input type="text" 
                     v-model="filters.descripcion" 
                     class="form-control border-start-0" 
                     placeholder="Filtrar por condiciones, alcance..." />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla Principal Minimalista -->
    <div class="card card-custom">
      <div class="card-body p-0">
        <!-- Spinner -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-cyan" role="status" style="width: 2.2rem; height: 2.2rem;"></div>
          <p class="text-muted mt-2 mb-0 small">Consultando base de licitaciones...</p>
        </div>

        <!-- Listado -->
        <div v-else-if="ofertas.length > 0" class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-header-minimal">
              <tr>
                <th class="ps-4">Consecutivo</th>
                <th>Objeto / Actividad</th>
                <th>Descripción</th>
                <th class="text-center">Inicio</th>
                <th class="text-center">Cierre</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-4">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="oferta in ofertas" :key="oferta.id" class="table-row-hover">
                <td class="ps-4">
                  <span class="badge-consecutivo">{{ oferta.consecutivo }}</span>
                </td>
                <td>
                  <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" :title="oferta.objeto">
                    {{ oferta.objeto }}
                  </div>
                  <div class="small text-muted text-truncate" style="max-width: 240px;" v-if="oferta.actividad" :title="oferta.actividad.producto">
                    <i class="fa-solid fa-tag me-1 text-cyan small"></i>{{ oferta.actividad.producto }}
                  </div>
                </td>
                <td>
                  <div class="text-muted small text-truncate" style="max-width: 280px;" :title="oferta.descripcion">
                    {{ oferta.descripcion }}
                  </div>
                </td>
                <td class="text-center">
                  <div class="fw-semibold small text-dark">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ oferta.hora_inicio }}</div>
                </td>
                <td class="text-center">
                  <div class="fw-semibold small text-dark">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ oferta.hora_cierre }}</div>
                </td>
                <td class="text-center">
                  <span class="badge-status-pill" 
                        :class="oferta.estado === 'Publicada' || oferta.estado === 'Activo' ? 'status-active' : 'status-inactive'">
                    <span class="status-dot"></span>
                    {{ oferta.estado }}
                  </span>
                </td>
                <td class="text-center pe-4">
                  <div class="d-flex justify-content-center gap-1">
                    <router-link :to="'/ofertas/detalle/' + oferta.id" 
                                 class="btn btn-sm btn-outline-minimal px-2 py-1" 
                                 title="Ver Detalle Completo">
                      <i class="fa-solid fa-eye text-cyan small me-1"></i>
                      <span class="small">Detalle</span>
                    </router-link>
                    <router-link :to="'/ofertas/editar/' + oferta.id" 
                                 class="btn btn-sm btn-outline-minimal px-2 py-1" 
                                 title="Editar Oferta y Gestionar Documentos">
                      <i class="fa-solid fa-pen-to-square small"></i>
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sin resultados -->
        <div v-else class="text-center py-5">
          <div class="empty-icon-box mx-auto mb-3">
            <i class="fa-regular fa-folder-open fa-2x text-muted opacity-50"></i>
          </div>
          <h5 class="fw-bold text-dark">No se encontraron ofertas</h5>
          <p class="text-muted mb-3 small">No hay registros que coincidan con los criterios de búsqueda.</p>
          <router-link to="/ofertas/crear" class="btn btn-suplos btn-sm">
            <i class="fa-solid fa-plus me-1 small"></i> Crear la primera oferta
          </router-link>
        </div>

        <!-- Paginación Minimalista -->
        <div v-if="pagination.total > 0" class="card-footer bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center py-3 px-4 gap-2">
          <div class="text-muted small">
            Mostrando <b class="text-dark">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</b>
            a <b class="text-dark">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</b>
            de <b class="text-dark">{{ pagination.total }}</b> registros
          </div>

          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <button class="page-link shadow-none" @click="cargarOfertas(pagination.current_page - 1)">
                <i class="fa-solid fa-chevron-left small"></i>
              </button>
            </li>
            <li class="page-item" 
                v-for="p in pagination.last_page" 
                :key="p" 
                :class="{ active: p === pagination.current_page }">
              <button class="page-link shadow-none" @click="cargarOfertas(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <button class="page-link shadow-none" @click="cargarOfertas(pagination.current_page + 1)">
                <i class="fa-solid fa-chevron-right small"></i>
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

<style scoped>
.table-header-minimal {
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.table-header-minimal th {
  font-size: 0.725rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  padding: 0.85rem 1rem;
  border-top: none;
}

.table-row-hover:hover {
  background-color: #f8fafc;
}

.badge-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.65rem;
  border-radius: 9999px;
}

.badge-status-pill .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.status-active {
  background-color: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.status-active .status-dot {
  background-color: #10b981;
}

.status-inactive {
  background-color: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.status-inactive .status-dot {
  background-color: #94a3b8;
}

.empty-icon-box {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background-color: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-link {
  color: #475569;
  border-color: #e2e8f0;
}

.page-item.active .page-link {
  background-color: var(--suplos-navy);
  border-color: var(--suplos-navy);
  color: #ffffff;
}
</style>
