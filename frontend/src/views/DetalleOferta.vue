<template>
  <div>
    <!-- Spinner de carga -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-2 small">Cargando detalle de la oferta...</p>
    </div>

    <div v-else-if="oferta">
      <!-- Navegación y Acciones -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
              <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none">Licitaciones</router-link></li>
              <li class="breadcrumb-item active" aria-current="page">{{ oferta.consecutivo }}</li>
            </ol>
          </nav>
          <div class="d-flex align-items-center gap-2">
            <h1 class="h4 fw-bold text-dark mb-0">{{ oferta.objeto }}</h1>
            <span class="badge bg-secondary">{{ oferta.consecutivo }}</span>
          </div>
        </div>
        
        <div class="d-flex gap-2">
          <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Editar Oferta</span>
          </router-link>
          <router-link to="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver al Listado</span>
          </router-link>
        </div>
      </div>

      <!-- Tarjeta Principal con Pestañas Nativas Bootstrap -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom pt-3 px-3">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
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
              <button class="nav-link d-flex align-items-center gap-2" 
                      :class="{ active: activeTab === 'documentos' }" 
                      @click="activeTab = 'documentos'">
                <span><i class="fa-solid fa-paperclip me-1"></i> Documentos Adjuntos</span>
                <span class="badge bg-secondary rounded-pill small">{{ oferta.documentos ? oferta.documentos.length : 0 }}</span>
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
                  <label class="text-uppercase small fw-bold text-muted mb-1">Objeto de la Oferta</label>
                  <div class="fs-5 fw-bold text-dark">{{ oferta.objeto }}</div>
                </div>

                <div class="mb-4">
                  <label class="text-uppercase small fw-bold text-muted mb-1">Descripción / Alcance</label>
                  <div class="p-3 bg-light rounded text-body border" style="white-space: pre-line;">
                    {{ oferta.descripcion }}
                  </div>
                </div>

                <div>
                  <label class="text-uppercase small fw-bold text-muted mb-2">Clasificación UNSPSC</label>
                  <div class="card border rounded bg-white" v-if="oferta.actividad">
                    <div class="card-body p-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary">UNSPSC {{ oferta.actividad.codigo_producto }}</span>
                        <span class="fw-bold text-dark">{{ oferta.actividad.producto }}</span>
                      </div>
                      <div class="row g-2 small text-muted">
                        <div class="col-md-4">
                          <strong>Segmento:</strong> {{ oferta.actividad.segmento || oferta.actividad.Segmento }}
                        </div>
                        <div class="col-md-4">
                          <strong>Familia:</strong> {{ oferta.actividad.familia }}
                        </div>
                        <div class="col-md-4">
                          <strong>Clase:</strong> {{ oferta.actividad.clase }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="card border-0 bg-light p-3 rounded mb-3">
                  <label class="text-uppercase small fw-bold text-muted mb-1">Presupuesto Estimado</label>
                  <div class="fs-4 fw-bold text-primary mb-1">
                    {{ formatearMoneda(oferta.presupuesto, oferta.moneda) }}
                  </div>
                  <div class="small text-muted">Moneda: <strong class="text-dark">{{ oferta.moneda }}</strong></div>
                </div>

                <div class="card border p-3 rounded">
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Estado de oferta:</span>
                    <EstadoBadge :estado="estadoActual" />
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Consecutivo:</span>
                    <span class="fw-bold text-dark">{{ oferta.consecutivo }}</span>
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
            <div class="row g-3">
              <div class="col-md-6">
                <div class="card border bg-light p-3 rounded h-100">
                  <div class="d-flex align-items-center gap-3 mb-2">
                    <i class="fa-solid fa-hourglass-start fa-2x text-primary"></i>
                    <div>
                      <div class="text-uppercase small fw-bold text-muted">Fecha y Hora de Apertura</div>
                      <div class="fs-4 fw-bold text-dark">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                      <div class="text-muted small">Hora: <strong class="text-dark">{{ oferta.hora_inicio }}</strong></div>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Habilitación para recepción y radicación de propuestas.</p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border bg-light p-3 rounded h-100">
                  <div class="d-flex align-items-center gap-3 mb-2">
                    <i class="fa-solid fa-hourglass-end fa-2x text-danger"></i>
                    <div>
                      <div class="text-uppercase small fw-bold text-muted">Fecha y Hora de Cierre</div>
                      <div class="fs-4 fw-bold text-danger">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                      <div class="text-muted small">Hora límite: <strong class="text-dark">{{ oferta.hora_cierre }}</strong></div>
                      <span v-if="estaVencida" class="badge bg-danger mt-1">
                        <i class="fa-solid fa-lock me-1"></i> Convocatoria Cerrada
                      </span>
                    </div>
                  </div>
                  <p class="small text-muted mb-0">Vencimiento del plazo para entrega formal de propuestas.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- PESTAÑA 3: DOCUMENTOS ADJUNTOS -->
          <div v-else-if="activeTab === 'documentos'">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-dark mb-0 text-uppercase small">Documentos Vinculados</h6>
              <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-upload me-1"></i> Gestionar Documentos
              </router-link>
            </div>

            <div v-if="oferta.documentos && oferta.documentos.length > 0" class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                      <span class="badge bg-danger" v-if="doc.archivo && doc.archivo.endsWith('.pdf')">
                        <i class="fa-regular fa-file-pdf me-1"></i> PDF
                      </span>
                      <span class="badge bg-warning text-dark" v-else>
                        <i class="fa-regular fa-file-zipper me-1"></i> ZIP
                      </span>
                    </td>
                    <td class="text-muted small">{{ formatearFechaHora(doc.creado_en) }}</td>
                    <td class="text-center pe-3">
                      <a :href="'/ofertas/' + oferta.id + '/documentos/' + doc.id + '/descargar'" 
                         class="btn btn-sm btn-outline-primary"
                         title="Descargar archivo">
                        <i class="fa-solid fa-download me-1"></i> Descargar
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- EmptyState reutilizable -->
            <EmptyState 
              v-else 
              titulo="Sin documentos adjuntos" 
              mensaje="No se encontraron documentos adjuntos para esta oferta.">
              <router-link :to="'/ofertas/editar/' + oferta.id" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Adjuntar Documento
              </router-link>
            </EmptyState>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';
import { formatearMoneda, formatearFecha, formatearFechaHora } from '../utils/formatters';
import { useOfertaEstado } from '../composables/useOfertaEstado';
import EstadoBadge from '../components/EstadoBadge.vue';
import EmptyState from '../components/EmptyState.vue';

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

const { obtenerEstado, estaCerrada } = useOfertaEstado(3000);

const estadoActual = computed(() => obtenerEstado(oferta.value));
const estaVencida = computed(() => estaCerrada(oferta.value));

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
      confirmButtonColor: '#0d6efd',
    }).then(() => router.push('/ofertas'));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  cargarDetalle();
});
</script>
