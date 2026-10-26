<template>
  <div>
    <!-- Encabezado Minimalista -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none text-muted">Licitaciones</router-link></li>
            <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Editar Oferta</li>
          </ol>
        </nav>
        <div class="d-flex align-items-center gap-3">
          <h1 class="h4 fw-bold text-navy mb-0">Editar Oferta</h1>
          <span class="badge-consecutivo" v-if="form.consecutivo">{{ form.consecutivo }}</span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <router-link :to="'/ofertas/detalle/' + id" class="btn btn-outline-minimal btn-sm d-flex align-items-center gap-2">
          <i class="fa-solid fa-eye small text-cyan"></i>
          <span>Ver Detalle</span>
        </router-link>
        <router-link to="/ofertas" class="btn btn-outline-minimal btn-sm d-flex align-items-center gap-2">
          <i class="fa-solid fa-arrow-left small"></i>
          <span>Volver al Listado</span>
        </router-link>
      </div>
    </div>

    <!-- Spinner de carga -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-cyan" role="status" style="width: 2.2rem; height: 2.2rem;"></div>
      <p class="text-muted mt-2 small">Cargando datos de la oferta...</p>
    </div>

    <form v-else @submit.prevent="actualizarOferta" novalidate>
      <!-- Alerta informativa si la oferta está cerrada -->
      <div v-if="form.estado === 'Cerrada'" class="alert alert-danger border-0 bg-danger-subtle d-flex align-items-center gap-2 mb-4 py-2 px-3 rounded-3">
        <i class="fa-solid fa-lock text-danger"></i>
        <span class="small fw-semibold text-danger">
          Esta oferta se encuentra <strong>Cerrada</strong> debido a que ha expirado su cronograma límite. Si desea reactivarla, extienda la fecha y hora de cierre a un momento futuro y seleccione el estado "Publicada".
        </span>
      </div>

      <!-- SECCIÓN 1: INFORMACIÓN BÁSICA (PRECARGADA) -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="section-title">
            <span class="section-icon">
              <i class="fa-solid fa-file-lines"></i>
            </span>
            <span>SECCIÓN 1 &mdash; Información Básica</span>
          </div>
          <span class="badge bg-light text-muted border fw-medium px-2 py-1">
            Consecutivo: {{ form.consecutivo }}
          </span>
        </div>
        
        <div class="card-body p-4">
          <div class="row g-4">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Objeto de la Oferta <span class="text-danger">*</span></label>
                <span class="char-counter-tag" :class="{ 'limit-near': form.objeto.length > 130, 'limit-reached': form.objeto.length >= 150 }">
                  {{ form.objeto.length }} / 150
                </span>
              </div>
              <input type="text" 
                     v-model="form.objeto" 
                     maxlength="150" 
                     class="form-control" 
                     :class="{ 'is-invalid': form.objeto.length > 0 && !isObjetoValido }"
                     required />
            </div>

            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Descripción / Alcance <span class="text-danger">*</span></label>
                <span class="char-counter-tag" :class="{ 'limit-near': form.descripcion.length > 360, 'limit-reached': form.descripcion.length >= 400 }">
                  {{ form.descripcion.length }} / 400
                </span>
              </div>
              <textarea v-model="form.descripcion" 
                        maxlength="400" 
                        rows="3" 
                        class="form-control" 
                        :class="{ 'is-invalid': form.descripcion.length > 0 && !isDescripcionValida }"
                        required></textarea>
            </div>

            <div class="col-md-3">
              <label class="form-label">Moneda <span class="text-danger">*</span></label>
              <select v-model="form.moneda" class="form-select" required>
                <option value="COP">COP (Peso Colombiano)</option>
                <option value="USD">USD (Dólar)</option>
                <option value="EUR">EUR (Euro)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Presupuesto Estimado <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-white text-navy border-end-0 small fw-bold">{{ form.moneda }}</span>
                <input type="text" 
                       v-model="form.presupuesto" 
                       class="form-control border-start-0" 
                       :class="{ 'is-invalid': form.presupuesto !== '' && !isPresupuestoValido }"
                       @keydown="onPresupuestoKeydown"
                       @input="onPresupuestoInput"
                       @paste="onPresupuestoPaste"
                       required />
              </div>
            </div>

            <div class="col-md-2">
              <label class="form-label">Estado</label>
              <select v-model="form.estado" class="form-select">
                <option value="Publicada">Publicada</option>
                <option value="Borrador">Borrador</option>
                <option value="Cerrada">Cerrada</option>
                <option value="Cancelada">Cancelada</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Actividad (Clasificador UNSPSC) <span class="text-danger">*</span></label>
              <BuscadorActividad 
                v-model="form.actividad_id" 
                :actividad-inicial="actividadInicial"
                :disabled="guardando"
                placeholder="Buscar por producto, código UNSPSC o segmento..."
              />
              <div class="form-text small text-muted">
                <i class="fa-solid fa-circle-info text-cyan me-1"></i> Buscador interactivo en vivo del catálogo UNSPSC.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: CRONOGRAMA -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="section-title">
            <span class="section-icon">
              <i class="fa-solid fa-calendar-days"></i>
            </span>
            <span>SECCIÓN 2 &mdash; Cronograma</span>
          </div>
        </div>

        <div class="card-body p-4">
          <div v-if="errorCronograma" class="alert alert-warning border-0 bg-warning-subtle d-flex align-items-center gap-2 mb-4 py-2 px-3 rounded-3">
            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
            <span class="small fw-semibold text-dark">{{ errorCronograma }}</span>
          </div>

          <div class="row g-4">
            <div class="col-md-3">
              <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
              <input type="date" v-model="form.fecha_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label">Hora de Inicio (24h) <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label">Fecha de Cierre <span class="text-danger">*</span></label>
              <input type="date" v-model="form.fecha_cierre" :min="form.fecha_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label">Hora de Cierre (24h) <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_cierre" class="form-control" required />
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: DOCUMENTOS (REGLA OBLIGATORIA) -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="section-title">
            <span class="section-icon">
              <i class="fa-solid fa-paperclip"></i>
            </span>
            <span>SECCIÓN 3 &mdash; Documentos de la Oferta</span>
          </div>
          <button type="button" 
                  class="btn btn-sm btn-suplos d-flex align-items-center gap-2" 
                  data-bs-toggle="modal" 
                  data-bs-target="#modalDocVue">
            <i class="fa-solid fa-plus small"></i>
            <span>Agregar Documento</span>
          </button>
        </div>

        <div class="card-body p-4">
          <!-- Alerta de regla obligatoria si no hay documentos -->
          <div v-if="documentos.length === 0" class="alert-requirement-warning mb-3 p-3 rounded-3">
            <div class="d-flex align-items-center gap-3">
              <i class="fa-solid fa-circle-exclamation text-danger fa-lg"></i>
              <div class="small text-danger fw-semibold">
                Regla obligatoria de edición: Debe existir al menos un (1) documento adjunto (PDF o ZIP) para poder guardar los cambios de la oferta.
              </div>
            </div>
          </div>

          <!-- Tabla minimalista de documentos -->
          <div v-if="documentos.length > 0" class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-header-minimal">
                <tr>
                  <th class="ps-3">Título</th>
                  <th>Descripción</th>
                  <th>Formato</th>
                  <th>Fecha Registro</th>
                  <th class="text-center pe-3">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="doc in documentos" :key="doc.id">
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
                    <div class="d-flex justify-content-center gap-1">
                      <a :href="'/ofertas/' + id + '/documentos/' + doc.id + '/descargar'" 
                         target="_blank" 
                         class="btn btn-sm btn-outline-minimal d-inline-flex align-items-center gap-1"
                         title="Descargar archivo original">
                        <i class="fa-solid fa-download small text-cyan"></i>
                        <span>Descargar</span>
                      </a>
                      <button type="button" 
                              @click="eliminarDocumento(doc.id)" 
                              class="btn btn-sm btn-outline-danger border-0" 
                              title="Eliminar documento">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="text-center py-4 text-muted">
            <div class="empty-icon-box mx-auto mb-2">
              <i class="fa-regular fa-file-pdf fa-lg text-muted opacity-50"></i>
            </div>
            <p class="mb-2 small">Aún no se ha cargado ningún documento adjunto.</p>
            <button type="button" class="btn btn-outline-minimal btn-sm" data-bs-toggle="modal" data-bs-target="#modalDocVue">
              <i class="fa-solid fa-upload me-1 text-cyan"></i> Cargar documento obligatorio
            </button>
          </div>
        </div>
      </div>

      <!-- Barra de acciones minimalista -->
      <div class="card card-custom">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
          <div class="small">
            <span v-if="documentos.length === 0" class="text-danger fw-semibold d-flex align-items-center gap-1">
              <i class="fa-solid fa-circle-exclamation"></i> Adjunte al menos un documento para habilitar el guardado.
            </span>
            <span v-else class="text-success fw-semibold d-flex align-items-center gap-1">
              <i class="fa-solid fa-circle-check"></i> Requisito cumplido ({{ documentos.length }} documento(s) adjunto(s)).
            </span>
          </div>

          <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
            <router-link to="/ofertas" class="btn btn-outline-minimal px-4">Cancelar</router-link>
            
            <button type="submit" 
                    class="btn btn-suplos px-4 d-flex align-items-center gap-2 shadow-none"
                    :disabled="!isFormValido || guardando">
              <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-floppy-disk"></i>
              <span>{{ guardando ? 'Guardando...' : 'Guardar Cambios' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Modal Component -->
    <ModalDocumento :oferta-id="id" @documento-agregado="onDocumentoAgregado" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import ModalDocumento from '../components/ModalDocumento.vue';
import BuscadorActividad from '../components/BuscadorActividad.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
});

const router = useRouter();

const form = ref({
  consecutivo: '',
  objeto: '',
  descripcion: '',
  moneda: 'COP',
  presupuesto: '',
  actividad_id: '',
  fecha_inicio: '',
  hora_inicio: '08:00',
  fecha_cierre: '',
  hora_cierre: '18:00',
  estado: 'Publicada',
});

const actividades = ref([]);
const actividadInicial = ref(null);
const documentos = ref([]);
const loading = ref(true);
const loadingActividades = ref(false);
const guardando = ref(false);

const isObjetoValido = computed(() => form.value.objeto.trim().length > 0 && form.value.objeto.length <= 150);
const isDescripcionValida = computed(() => form.value.descripcion.trim().length > 0 && form.value.descripcion.length <= 400);
const isPresupuestoValido = computed(() => {
  const val = String(form.value.presupuesto).trim();
  return val && /^\d+(\.\d{1,2})?$/.test(val) && parseFloat(val) > 0;
});

const onPresupuestoKeydown = (event) => {
  const allowedKeys = ['Backspace', 'Tab', 'Enter', 'Escape', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
  if (allowedKeys.includes(event.key)) {
    return;
  }
  if (event.ctrlKey || event.metaKey) {
    return;
  }
  if (event.key === '.' || event.key === ',') {
    const currentVal = String(form.value.presupuesto || '');
    if (currentVal.includes('.')) {
      event.preventDefault();
    }
    return;
  }
  if (!/^\d$/.test(event.key)) {
    event.preventDefault();
  }
};

const onPresupuestoInput = (event) => {
  let val = String(event.target.value || '').replace(',', '.').replace(/[^0-9.]/g, '');
  const parts = val.split('.');
  if (parts.length > 2) {
    val = parts[0] + '.' + parts.slice(1).join('');
  }
  if (parts.length === 2 && parts[1].length > 2) {
    val = parts[0] + '.' + parts[1].substring(0, 2);
  }
  form.value.presupuesto = val;
};

const onPresupuestoPaste = (event) => {
  event.preventDefault();
  const pasted = (event.clipboardData || window.clipboardData).getData('text');
  let val = pasted.replace(',', '.').replace(/[^0-9.]/g, '');
  const parts = val.split('.');
  if (parts.length > 2) {
    val = parts[0] + '.' + parts.slice(1).join('');
  }
  if (parts.length === 2 && parts[1].length > 2) {
    val = parts[0] + '.' + parts[1].substring(0, 2);
  }
  form.value.presupuesto = val;
};

const errorCronograma = computed(() => {
  const { fecha_inicio, hora_inicio, fecha_cierre, hora_cierre, estado } = form.value;
  if (!fecha_inicio || !hora_inicio || !fecha_cierre || !hora_cierre) return null;
  if (fecha_cierre < fecha_inicio) return 'La fecha de cierre no puede ser menor a la fecha de inicio.';
  if (fecha_inicio === fecha_cierre && hora_cierre <= hora_inicio) return 'Si las fechas coinciden, la hora de cierre debe ser mayor a la de inicio.';
  if (estado === 'Publicada') {
    const fechaHoraCierre = new Date(`${fecha_cierre}T${hora_cierre.length === 5 ? hora_cierre + ':00' : hora_cierre}`);
    if (!isNaN(fechaHoraCierre) && fechaHoraCierre <= new Date()) {
      return 'Para mantener o cambiar la oferta a "Publicada", la fecha y hora de cierre deben ser posteriores al momento actual.';
    }
  }
  return null;
});

const isFormValido = computed(() => {
  return (
    isObjetoValido.value &&
    isDescripcionValida.value &&
    isPresupuestoValido.value &&
    form.value.actividad_id &&
    !errorCronograma.value &&
    documentos.value.length >= 1
  );
});

const cargarDatos = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/ofertas/${props.id}`);
    if (res.data.success) {
      const d = res.data.data;
      form.value.consecutivo  = d.consecutivo;
      form.value.objeto       = d.objeto;
      form.value.descripcion  = d.descripcion;
      form.value.moneda       = d.moneda;
      form.value.presupuesto  = d.presupuesto;
      form.value.actividad_id = d.actividad_id;
      actividadInicial.value  = d.actividad || null;
      form.value.fecha_inicio = d.fecha_inicio?.split('T')[0] || '';
      form.value.hora_inicio  = d.hora_inicio?.substring(0, 5) || '08:00';
      form.value.fecha_cierre = d.fecha_cierre?.split('T')[0] || '';
      form.value.hora_cierre  = d.hora_cierre?.substring(0, 5) || '18:00';
      form.value.estado       = d.estado;

      documentos.value        = d.documentos || [];
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

const cargarActividades = async () => {
  loadingActividades.value = true;
  try {
    const res = await api.get('/actividades');
    if (res.data.success) {
      actividades.value = res.data.data;
    }
  } finally {
    loadingActividades.value = false;
  }
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

const onDocumentoAgregado = (nuevoDoc) => {
  documentos.value.push(nuevoDoc);
};

const eliminarDocumento = async (docId) => {
  const confirm = await Swal.fire({
    title: '¿Eliminar documento?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#0b2853',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  });

  if (!confirm.isConfirmed) return;

  try {
    const res = await api.delete(`/ofertas/${props.id}/documentos/${docId}`);
    if (res.data.success) {
      documentos.value = documentos.value.filter(d => d.id !== docId);
      Swal.fire({
        icon: 'success',
        title: 'Documento Eliminado',
        timer: 1200,
        showConfirmButton: false,
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo eliminar el documento.',
      confirmButtonColor: '#0b2853',
    });
  }
};

const actualizarOferta = async () => {
  if (!isFormValido.value) return;

  guardando.value = true;
  try {
    const res = await api.post(`/ofertas/${props.id}`, form.value);
    if (res.data.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Actualización Exitosa',
        text: 'La oferta y sus documentos se guardaron correctamente.',
        confirmButtonColor: '#0b2853',
        timer: 1500,
        showConfirmButton: false,
      });
      router.push(`/ofertas/detalle/${props.id}`);
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error de validación',
      text: err.response?.data?.message || 'Error al actualizar la oferta.',
      confirmButtonColor: '#0b2853',
    });
  } finally {
    guardando.value = false;
  }
};

onMounted(() => {
  cargarDatos();
});
</script>

<style scoped>
.alert-requirement-warning {
  background-color: #fef2f2;
  border-left: 4px solid #ef4444;
  border-top: 1px solid #fee2e2;
  border-right: 1px solid #fee2e2;
  border-bottom: 1px solid #fee2e2;
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
