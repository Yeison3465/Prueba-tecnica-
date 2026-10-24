<template>
  <div>
    <!-- Spinner de carga -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-2">Cargando detalle de la oferta...</p>
    </div>

    <div v-else-if="oferta">
      <!-- Navegación y Acciones -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
              <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none text-muted">Ofertas</router-link></li>
              <li class="breadcrumb-item active" aria-current="page">{{ oferta.consecutivo }}</li>
            </ol>
          </nav>
          <div class="d-flex align-items-center gap-3">
            <h1 class="h3 fw-bold text-dark mb-0">{{ oferta.objeto }}</h1>
            <span class="badge-consecutivo fs-6">{{ oferta.consecutivo }}</span>
          </div>
        </div>
        
        <div class="d-flex gap-2">
          <!-- Botón para acceder al formulario de edición (REQUERIDO POR EL PDF) -->
          <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-suplos d-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Editar Oferta</span>
          </router-link>
          <router-link to="/ofertas" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver al Listado</span>
          </router-link>
        </div>
      </div>

      <!-- Tarjeta con Pestañas (Tabs) -->
      <div class="card card-custom">
        <div class="card-header bg-white border-bottom pt-3 px-4">
          <ul class="nav nav-tabs card-header-tabs" id="ofertaTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link" 
                      :class="{ active: activeTab === 'info' }" 
                      @click="activeTab = 'info'">
                <i class="fa-solid fa-circle-info me-1"></i> Información Básica
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" 
                      :class="{ active: activeTab === 'cronograma' }" 
                      @click="activeTab = 'cronograma'">
                <i class="fa-solid fa-calendar-days me-1"></i> Cronograma
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link d-flex align-items-center gap-1" 
                      :class="{ active: activeTab === 'documentos' }" 
                      @click="activeTab = 'documentos'">
                <i class="fa-solid fa-paperclip me-1"></i> Documentos Adjuntos
                <span class="badge bg-primary rounded-pill small">{{ oferta.documentos ? oferta.documentos.length : 0 }}</span>
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
                  <label class="text-uppercase small fw-bold text-muted mb-1">Objeto</label>
                  <div class="fs-5 fw-semibold text-dark">{{ oferta.objeto }}</div>
                </div>

                <div class="mb-4">
                  <label class="text-uppercase small fw-bold text-muted mb-1">Descripción / Alcance</label>
                  <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line;">
                    {{ oferta.descripcion }}
                  </div>
                </div>

                <div>
                  <label class="text-uppercase small fw-bold text-muted mb-2">Clasificación UNSPSC</label>
                  <div class="card border bg-light-subtle" v-if="oferta.actividad">
                    <div class="card-body p-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white">Producto: {{ oferta.actividad.codigo_producto }}</span>
                        <span class="fw-bold text-dark">{{ oferta.actividad.producto }}</span>
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
                <div class="card border-0 bg-light p-3 rounded-4 mb-3">
                  <label class="text-uppercase small fw-bold text-muted mb-1">Presupuesto Estimado</label>
                  <div class="fs-3 fw-bold text-primary">
                    {{ formatearMoneda(oferta.presupuesto, oferta.moneda) }}
                  </div>
                  <div class="small text-muted">Moneda oficial: <span class="fw-bold text-dark">{{ oferta.moneda }}</span></div>
                </div>

                <div class="card border p-3 rounded-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Estado actual:</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                      {{ oferta.estado }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Consecutivo:</span>
                    <span class="fw-bold">{{ oferta.consecutivo }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Registrado:</span>
                    <span class="small">{{ oferta.creado_en }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PESTAÑA 2: CRONOGRAMA -->
          <div v-else-if="activeTab === 'cronograma'">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="card border-0 bg-primary-subtle p-4 rounded-4 h-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary text-white p-3 rounded-circle">
                      <i class="fa-solid fa-hourglass-start fa-lg"></i>
                    </div>
                    <div>
                      <div class="text-uppercase small fw-bold text-primary">Apertura / Inicio</div>
                      <div class="fs-4 fw-bold text-dark">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                      <div class="text-muted small">Hora programada: <strong class="text-dark">{{ oferta.hora_inicio }}</strong></div>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Momento a partir del cual los oferentes pueden consultar las bases y presentar ofertas.</p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-0 bg-danger-subtle p-4 rounded-4 h-100">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-danger text-white p-3 rounded-circle">
                      <i class="fa-solid fa-hourglass-end fa-lg"></i>
                    </div>
                    <div>
                      <div class="text-uppercase small fw-bold text-danger">Cierre de Convocatoria</div>
                      <div class="fs-4 fw-bold text-dark">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                      <div class="text-muted small">Hora límite: <strong class="text-dark">{{ oferta.hora_cierre }}</strong></div>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Fecha y hora improrrogable para la radicación y cierre de propuestas.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- PESTAÑA 3: DOCUMENTOS ADJUNTOS -->
          <div v-else-if="activeTab === 'documentos'">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-dark mb-0">Documentos Relacionados</h6>
              <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-upload me-1"></i> Gestionar Documentos
              </router-link>
            </div>

            <div v-if="oferta.documentos && oferta.documentos.length > 0" class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                  <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Formato</th>
                    <th>Fecha de Registro</th>
                    <th class="text-center">Descarga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="doc in oferta.documentos" :key="doc.id">
                    <td class="fw-semibold text-dark">{{ doc.titulo }}</td>
                    <td class="text-muted small">{{ doc.descripcion }}</td>
                    <td>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle" v-if="doc.archivo.endsWith('.pdf')">
                        <i class="fa-regular fa-file-pdf me-1"></i> PDF
                      </span>
                      <span class="badge bg-warning-subtle text-dark border border-warning-subtle" v-else>
                        <i class="fa-regular fa-file-zipper me-1"></i> ZIP
                      </span>
                    </td>
                    <td class="text-muted small">{{ doc.creado_en }}</td>
                    <td class="text-center">
                      <a :href="'/ofertas/' + oferta.id + '/documentos/' + doc.id + '/descargar'" 
                         class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-download"></i>
                        <span>Descargar</span>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="text-center py-5 text-muted">
              <i class="fa-regular fa-folder-open fa-2x mb-2 opacity-50"></i>
              <p class="mb-0">No se encontraron documentos adjuntos para esta oferta.</p>
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

onMounted(() => {
  cargarDetalle();
});
</script>
