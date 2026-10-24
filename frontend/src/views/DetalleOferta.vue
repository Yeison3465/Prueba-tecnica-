<template>
  <div>
    <!-- Spinner de carga -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-cyan" role="status" style="width: 2.2rem; height: 2.2rem;"></div>
      <p class="text-muted mt-2 small">Cargando detalle de la oferta...</p>
    </div>

    <div v-else-if="oferta">
      <!-- Navegación y Acciones -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
              <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none text-muted">Licitaciones</router-link></li>
              <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ oferta.consecutivo }}</li>
            </ol>
          </nav>
          <div class="d-flex align-items-center gap-3">
            <h1 class="h4 fw-bold text-navy mb-0">{{ oferta.objeto }}</h1>
            <span class="badge-consecutivo">{{ oferta.consecutivo }}</span>
          </div>
        </div>
        
        <div class="d-flex gap-2">
          <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-suplos btn-sm d-flex align-items-center gap-2 shadow-none">
            <i class="fa-solid fa-pen-to-square small"></i>
            <span>Editar Oferta</span>
          </router-link>
          <router-link to="/ofertas" class="btn btn-outline-minimal btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-left small"></i>
            <span>Volver al Listado</span>
          </router-link>
        </div>
      </div>

      <!-- Tarjeta Principal con Pestañas Minimalistas Suplos -->
      <div class="card card-custom">
        <div class="card-header bg-white border-bottom pt-3 px-4">
          <ul class="nav nav-tabs border-0" id="ofertaTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link border-0 fw-semibold px-3 py-2 tab-btn-minimal" 
                      :class="{ 'tab-active': activeTab === 'info' }" 
                      @click="activeTab = 'info'">
                <i class="fa-solid fa-circle-info me-1 small"></i> Información Básica
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link border-0 fw-semibold px-3 py-2 tab-btn-minimal" 
                      :class="{ 'tab-active': activeTab === 'cronograma' }" 
                      @click="activeTab = 'cronograma'">
                <i class="fa-solid fa-calendar-days me-1 small"></i> Cronograma
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link border-0 fw-semibold px-3 py-2 tab-btn-minimal d-flex align-items-center gap-2" 
                      :class="{ 'tab-active': activeTab === 'documentos' }" 
                      @click="activeTab = 'documentos'">
                <i class="fa-solid fa-paperclip small"></i> Documentos Adjuntos
                <span class="badge rounded-pill bg-light text-navy border small">{{ oferta.documentos ? oferta.documentos.length : 0 }}</span>
              </button>
            </li>
          </ul>
        </div>

        <div class="card-body p-4">
          <!-- PESTAÑA 1: INFORMACIÓN BÁSICA -->
          <div v-if="activeTab === 'info'">
            <div class="row g-4">
              <div class="col-md-8">
                <div class="mb-4">
                  <label class="text-uppercase small fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Objeto de la Oferta</label>
                  <div class="fs-5 fw-bold text-dark">{{ oferta.objeto }}</div>
                </div>

                <div class="mb-4">
                  <label class="text-uppercase small fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Descripción / Alcance</label>
                  <div class="p-3 bg-light rounded-3 text-body border" style="white-space: pre-line; font-size: 0.92rem; line-height: 1.6;">
                    {{ oferta.descripcion }}
                  </div>
                </div>

                <div>
                  <label class="text-uppercase small fw-bold text-muted mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Clasificación de Actividad (UNSPSC)</label>
                  <div class="card border rounded-3 bg-white" v-if="oferta.actividad">
                    <div class="card-body p-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge-consecutivo">Cód. {{ oferta.actividad.codigo_producto }}</span>
                        <span class="fw-bold text-navy">{{ oferta.actividad.producto }}</span>
                      </div>
                      <div class="row g-2 small text-muted">
                        <div class="col-md-4">
                          <strong>Segmento:</strong> {{ oferta.actividad.Segmento }} ({{ oferta.actividad.codigo_segmento }})
                        </div>
                        <div class="col-md-4">
                          <strong>Familia:</strong> {{ oferta.actividad.familia }} ({{ oferta.actividad.codigo_familia }})
                        </div>
                        <div class="col-md-4">
                          <strong>Clase:</strong> {{ oferta.actividad.clase }} ({{ oferta.actividad.codigo_clase }})
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="card border-0 bg-light p-4 rounded-3 mb-3">
                  <label class="text-uppercase small fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Presupuesto Estimado</label>
                  <div class="fs-4 fw-bold text-navy mb-1">
                    {{ formatearMoneda(oferta.presupuesto, oferta.moneda) }}
                  </div>
                  <div class="small text-muted">Moneda de cotización: <strong class="text-dark">{{ oferta.moneda }}</strong></div>
                </div>

                <div class="card border p-3 rounded-3">
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Estado de licitación:</span>
                    <span class="badge rounded-pill fw-semibold px-3 py-1" style="background-color: #ecfdf5; color: #059669; border: 1px solid #d1fae5;">
                      ● {{ oferta.estado }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Consecutivo:</span>
                    <span class="fw-bold text-navy">{{ oferta.consecutivo }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Fecha registro:</span>
                    <span class="small text-dark fw-medium">{{ formatearFechaHora(oferta.creado_en) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PESTAÑA 2: CRONOGRAMA -->
          <div v-else-if="activeTab === 'cronograma'">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="card border bg-white p-4 rounded-3 h-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="icon-circle-bg" style="background-color: #f0f9ff; color: #009fe3;">
                      <i class="fa-solid fa-hourglass-start fa-lg"></i>
                    </div>
                    <div>
                      <div class="text-uppercase small fw-bold text-muted" style="font-size: 0.72rem;">Apertura de la Convocatoria</div>
                      <div class="fs-4 fw-bold text-navy">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                      <div class="text-muted small">Hora programada: <strong class="text-dark">{{ oferta.hora_inicio }}</strong></div>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Habilitación para recepción y radicación de propuestas técnicas y económicas.</p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border bg-white p-4 rounded-3 h-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="icon-circle-bg" style="background-color: #fef2f2; color: #ef4444;">
                      <i class="fa-solid fa-hourglass-end fa-lg"></i>
                    </div>
                    <div>
                      <div class="text-uppercase small fw-bold text-muted" style="font-size: 0.72rem;">Cierre de Convocatoria</div>
                      <div class="fs-4 fw-bold text-danger">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                      <div class="text-muted small">Hora límite: <strong class="text-dark">{{ oferta.hora_cierre }}</strong></div>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Vencimiento del plazo para la entrega formal de documentación requerida.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- PESTAÑA 3: DOCUMENTOS ADJUNTOS -->
          <div v-else-if="activeTab === 'documentos'">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-navy mb-0 small text-uppercase" style="letter-spacing: 0.5px;">Documentos Vinculados</h6>
              <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-outline-minimal btn-sm">
                <i class="fa-solid fa-upload me-1 text-cyan small"></i> Gestionar Documentos
              </router-link>
            </div>

            <div v-if="oferta.documentos && oferta.documentos.length > 0" class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-header-minimal">
                  <tr>
                    <th class="ps-3">Título</th>
                    <th>Descripción</th>
                    <th>Formato</th>
                    <th>Fecha de Registro</th>
                    <th class="text-center pe-3">Descarga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="doc in oferta.documentos" :key="doc.id">
                    <td class="ps-3 fw-bold text-dark">{{ doc.titulo }}</td>
                    <td class="text-muted small">{{ doc.descripcion }}</td>
                    <td>
                      <span class="badge border" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca !important;" v-if="doc.archivo && doc.archivo.endsWith('.pdf')">
                        <i class="fa-regular fa-file-pdf me-1"></i> PDF
                      </span>
                      <span class="badge border" style="background-color: #fffbeb; color: #d97706; border-color: #fef3c7 !important;" v-else>
                        <i class="fa-regular fa-file-zipper me-1"></i> ZIP
                      </span>
                    </td>
                    <td class="text-muted small">{{ formatearFechaHora(doc.creado_en) }}</td>
                    <td class="text-center pe-3">
                      <a :href="'/ofertas/' + oferta.id + '/documentos/' + doc.id + '/descargar'" 
                         class="btn btn-sm btn-outline-minimal d-inline-flex align-items-center gap-1"
                         title="Descargar archivo adjunto">
                        <i class="fa-solid fa-download small text-cyan"></i>
                        <span>Descargar</span>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="text-center py-5 text-muted">
              <div class="empty-icon-box mx-auto mb-2">
                <i class="fa-regular fa-folder-open fa-lg text-muted opacity-50"></i>
              </div>
              <p class="mb-0 small">No se encontraron documentos adjuntos para esta oferta.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
});

const router = useRouter();
const oferta = ref(null);
const loading = ref(true);
const activeTab = ref('info');

const cargarDetalle = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/ofertas/${props.id}`);
    if (res.data.success) {
      oferta.value = res.data.data;
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo cargar la oferta solicitada.',
      confirmButtonColor: '#0b2853',
    }).then(() => router.push('/ofertas'));
  } finally {
    loading.value = false;
  }
};

const formatearMoneda = (val, moneda = 'COP') => {
  if (!val) return '$ 0.00';
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: moneda,
    minimumFractionDigits: 2,
  }).format(val);
};

const formatearFecha = (str) => {
  if (!str) return 'N/A';
  return str.split('T')[0];
};

const formatearFechaHora = (str) => {
  if (!str) return 'N/A';
  const parts = str.split('T');
  if (parts.length === 2) {
    const hora = parts[1].substring(0, 5);
    return `${parts[0]} ${hora}`;
  }
  return str.substring(0, 16);
};

onMounted(() => {
  cargarDetalle();
});
</script>

<style scoped>
.tab-btn-minimal {
  color: #64748b;
  position: relative;
  transition: all 0.15s ease;
}

.tab-btn-minimal:hover {
  color: #0b2853;
}

.tab-btn-minimal.tab-active {
  color: #0b2853 !important;
  font-weight: 700;
  border-bottom: 2px solid #009fe3 !important;
}

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

.icon-circle-bg {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-icon-box {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background-color: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
