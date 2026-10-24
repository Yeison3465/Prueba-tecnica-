<template>
  <div>
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none text-muted">Ofertas</router-link></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Oferta</li>
          </ol>
        </nav>
        <div class="d-flex align-items-center gap-3">
          <h1 class="h3 fw-bold text-dark mb-0">Formulario de Edición de Oferta</h1>
          <span class="badge-consecutivo" v-if="form.consecutivo">{{ form.consecutivo }}</span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <router-link :to="'/ofertas/detalle/' + id" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
          <i class="fa-solid fa-eye"></i>
          <span>Ver Detalle</span>
        </router-link>
        <router-link to="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Volver al Listado</span>
        </router-link>
      </div>
    </div>

    <!-- Spinner de carga inicial -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-2">Cargando datos de la oferta...</p>
    </div>

    <form v-else @submit.prevent="actualizarOferta" novalidate>
      <!-- SECCIÓN 1: INFORMACIÓN BÁSICA (PRECARGADA) -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2 text-primary fw-bold">
            <i class="fa-solid fa-circle-info"></i>
            <span>SECCIÓN 1 – Información Básica</span>
          </div>
          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
            Consecutivo: {{ form.consecutivo }}
          </span>
        </div>
        
        <div class="card-body p-4">
          <div class="row g-4">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Objeto de la Oferta <span class="text-danger">*</span></label>
                <span class="char-counter" :class="{ 'limit-near': form.objeto.length > 130, 'limit-reached': form.objeto.length >= 150 }">
                  {{ form.objeto.length }} / 150 caracteres
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
                <span class="char-counter" :class="{ 'limit-near': form.descripcion.length > 360, 'limit-reached': form.descripcion.length >= 400 }">
                  {{ form.descripcion.length }} / 400 caracteres
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
                <option value="USD">USD (Dólar Estadounidense)</option>
                <option value="EUR">EUR (Euro)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Presupuesto <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted">{{ form.moneda }}</span>
                <input type="text" 
                       v-model="form.presupuesto" 
                       class="form-control" 
                       :class="{ 'is-invalid': form.presupuesto !== '' && !isPresupuestoValido }"
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
              <label class="form-label">Actividad UNSPSC <span class="text-danger">*</span></label>
              <select v-model="form.actividad_id" class="form-select" :disabled="loadingActividades" required>
                <option value="" disabled>-- Seleccione una actividad --</option>
                <option v-for="act in actividades" :key="act.id" :value="act.id">
                  {{ act.producto }} (Cód. {{ act.codigo_producto }})
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: CRONOGRAMA -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2 text-primary fw-bold">
            <i class="fa-solid fa-calendar-days"></i>
            <span>SECCIÓN 2 – Cronograma</span>
          </div>
        </div>

        <div class="card-body p-4">
          <div v-if="errorCronograma" class="alert alert-warning d-flex align-items-center gap-2 mb-4 py-2">
            <i class="fa-solid fa-triangle-exclamation text-warning fa-lg"></i>
            <span class="small fw-semibold">{{ errorCronograma }}</span>
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

      <!-- SECCIÓN 3: DOCUMENTOS (REQUERIMIENTO OBLIGATORIO) -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2 text-primary fw-bold">
            <i class="fa-solid fa-paperclip"></i>
            <span>SECCIÓN 3 – Documentos de la Oferta</span>
          </div>
          <button type="button" 
                  class="btn btn-sm btn-suplos d-flex align-items-center gap-2" 
                  data-bs-toggle="modal" 
                  data-bs-target="#modalDocVue">
            <i class="fa-solid fa-plus"></i>
            <span>Agregar Documento</span>
          </button>
        </div>

        <div class="card-body p-4">
          <!-- Alerta de regla obligatoria del PDF -->
          <div v-if="documentos.length === 0" class="alert alert-danger d-flex align-items-center gap-3 mb-3">
            <i class="fa-solid fa-triangle-exclamation fa-2x text-danger"></i>
            <div>
              <div class="fw-bold">Regla obligatoria de edición:</div>
              <div class="small">En edición debe existir al menos 1 documento cargado (PDF o ZIP). Debe agregar un documento para poder guardar cambios.</div>
            </div>
          </div>

          <!-- Tabla de documentos -->
          <div v-if="documentos.length > 0" class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light small text-uppercase text-muted">
                <tr>
                  <th>Título</th>
                  <th>Descripción</th>
                  <th>Archivo</th>
                  <th>Fecha Registro</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="doc in documentos" :key="doc.id">
                  <td class="fw-semibold text-dark">{{ doc.titulo }}</td>
                  <td class="text-muted small">{{ doc.descripcion }}</td>
                  <td>
                    <a :href="'/ofertas/' + id + '/documentos/' + doc.id + '/descargar'" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                      <i class="fa-solid fa-download"></i>
                      <span>Descargar archivo</span>
                    </a>
                  </td>
                  <td class="text-muted small">{{ doc.creado_en }}</td>
                  <td class="text-center">
                    <button type="button" 
                            @click="eliminarDocumento(doc.id)" 
                            class="btn btn-sm btn-outline-danger" 
                            title="Eliminar documento">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="text-center py-4 text-muted">
            <i class="fa-regular fa-file-pdf fa-2x mb-2 text-danger opacity-50"></i>
            <p class="mb-2">Aún no se ha cargado ningún documento adjunto.</p>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDocVue">
              <i class="fa-solid fa-upload me-1"></i> Cargar documento obligatorio
            </button>
          </div>
        </div>
      </div>

      <!-- Barra de acciones -->
      <div class="card card-custom">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
          <div class="small">
            <span v-if="documentos.length === 0" class="text-danger fw-bold">
              <i class="fa-solid fa-circle-exclamation me-1"></i> Falta adjuntar al menos un documento para habilitar el guardado.
            </span>
            <span v-else class="text-success fw-semibold">
              <i class="fa-solid fa-circle-check me-1"></i> Requisito de documentación cumplido ({{ documentos.length }} documentos registrados).
            </span>
          </div>

          <div class="d-flex gap-2">
            <router-link to="/ofertas" class="btn btn-outline-secondary px-4">Cancelar</router-link>
            
            <!-- Botón Guardar Cambios: Bloqueado si no hay al menos 1 documento -->
            <button type="submit" 
                    class="btn btn-suplos px-4 d-flex align-items-center gap-2 shadow-sm"
                    :disabled="!isFormValido || guardando">
              <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-floppy-disk"></i>
              <span>{{ guardando ? 'Guardando...' : 'Guardar Cambios' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Componente Modal para agregar documento -->
    <ModalDocumento :oferta-id="id" @documento-agregado="onDocumentoAgregado" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import ModalDocumento from '../components/ModalDocumento.vue';
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
const documentos = ref([]);
const loading = ref(true);
const loadingActividades = ref(true);
const guardando = ref(false);

const isObjetoValido = computed(() => form.value.objeto.trim().length > 0 && form.value.objeto.length <= 150);
const isDescripcionValida = computed(() => form.value.descripcion.trim().length > 0 && form.value.descripcion.length <= 400);
const isPresupuestoValido = computed(() => {
  const val = String(form.value.presupuesto).trim();
  return val && /^\d+(\.\d{1,2})?$/.test(val) && parseFloat(val) > 0;
});

const errorCronograma = computed(() => {
  const { fecha_inicio, hora_inicio, fecha_cierre, hora_cierre } = form.value;
  if (!fecha_inicio || !hora_inicio || !fecha_cierre || !hora_cierre) return null;
  if (fecha_cierre < fecha_inicio) return 'La fecha de cierre no puede ser menor a la fecha de inicio.';
  if (fecha_inicio === fecha_cierre && hora_cierre <= hora_inicio) return 'Si las fechas coinciden, la hora de cierre debe ser mayor a la de inicio.';
  return null;
});

// REGLA DEL PDF: En edición debe existir al menos 1 documento
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

const onDocumentoAgregado = (nuevoDoc) => {
  documentos.value.push(nuevoDoc);
};

const eliminarDocumento = async (docId) => {
  const confirm = await Swal.fire({
    title: '¿Eliminar documento?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
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
        title: 'Documento eliminado',
        timer: 1500,
        showConfirmButton: false,
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo eliminar el documento.',
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
        title: '¡Actualizado con éxito!',
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
    });
  } finally {
    guardando.value = false;
  }
};

onMounted(() => {
  cargarActividades();
  cargarDatos();
});
</script>
