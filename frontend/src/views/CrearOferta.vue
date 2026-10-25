<template>
  <div>
    <!-- Encabezado Minimalista -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none text-muted">Licitaciones</router-link></li>
            <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Nueva Oferta</li>
          </ol>
        </nav>
        <h1 class="h4 fw-bold text-navy mb-0">Crear Nueva Oferta</h1>
      </div>
      <div>
        <router-link to="/ofertas" class="btn btn-outline-minimal btn-sm d-flex align-items-center gap-2">
          <i class="fa-solid fa-arrow-left small"></i>
          <span>Volver al Listado</span>
        </router-link>
      </div>
    </div>

    <form @submit.prevent="guardarOferta" novalidate>
      <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
      <div class="card card-custom mb-4">
        <div class="card-custom-header d-flex justify-content-between align-items-center">
          <div class="section-title">
            <span class="section-icon">
              <i class="fa-solid fa-file-lines"></i>
            </span>
            <span>SECCIÓN 1 &mdash; Información Básica</span>
          </div>
          <span class="badge bg-light text-muted border fw-medium px-2 py-1">Paso 1 de 2</span>
        </div>
        
        <div class="card-body p-4">
          <div class="row g-4">
            <!-- Objeto (Máx 150 caracteres con contador reactivo) -->
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">
                  Objeto de la Oferta <span class="text-danger">*</span>
                </label>
                <span class="char-counter-tag" :class="{ 'limit-near': form.objeto.length > 130, 'limit-reached': form.objeto.length >= 150 }">
                  {{ form.objeto.length }} / 150
                </span>
              </div>
              <input type="text" 
                     v-model="form.objeto" 
                     maxlength="150" 
                     class="form-control" 
                     :class="{ 'is-invalid': form.objeto.length > 0 && !isObjetoValido }"
                     placeholder="Ej. Adquisición e implementación de licencias de computación en la nube..." 
                     required />
              <div class="invalid-feedback">
                El objeto es obligatorio, no debe contener solo espacios y no puede superar 150 caracteres.
              </div>
            </div>

            <!-- Descripción (Máx 400 caracteres con contador reactivo) -->
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">
                  Descripción / Alcance <span class="text-danger">*</span>
                </label>
                <span class="char-counter-tag" :class="{ 'limit-near': form.descripcion.length > 360, 'limit-reached': form.descripcion.length >= 400 }">
                  {{ form.descripcion.length }} / 400
                </span>
              </div>
              <textarea v-model="form.descripcion" 
                        maxlength="400" 
                        rows="3" 
                        class="form-control" 
                        :class="{ 'is-invalid': form.descripcion.length > 0 && !isDescripcionValida }"
                        placeholder="Detalle de los requerimientos técnicos, especificaciones y términos generales..." 
                        required></textarea>
              <div class="invalid-feedback">
                La descripción es obligatoria, no debe contener solo espacios y no puede superar 400 caracteres.
              </div>
            </div>

            <!-- Moneda -->
            <div class="col-md-3">
              <label class="form-label">Moneda <span class="text-danger">*</span></label>
              <select v-model="form.moneda" class="form-select" required>
                <option value="COP">COP (Peso Colombiano)</option>
                <option value="USD">USD (Dólar Americano)</option>
                <option value="EUR">EUR (Euro)</option>
              </select>
            </div>

            <!-- Presupuesto -->
            <div class="col-md-4">
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
                       placeholder="0.00" 
                       required />
              </div>
              <div class="invalid-feedback d-block small" v-if="form.presupuesto !== '' && !isPresupuestoValido">
                Debe ser numérico mayor a 0 con máximo dos decimales (ej. 1500000.50).
              </div>
            </div>

            <!-- Actividad UNSPSC -->
            <div class="col-md-5">
              <label class="form-label">Actividad (Clasificador UNSPSC) <span class="text-danger">*</span></label>
              <select v-model="form.actividad_id" 
                      class="form-select" 
                      :disabled="loadingActividades" 
                      required>
                <option value="" disabled>
                  {{ loadingActividades ? 'Consultando catálogo UNSPSC...' : '-- Seleccione una actividad del catálogo --' }}
                </option>
                <option v-for="act in actividades" :key="act.id" :value="act.id">
                  {{ act.producto }} ({{ act.codigo_producto }}) &mdash; {{ act.segmento || act.Segmento }}
                </option>
              </select>
              <div class="form-text small text-muted" v-if="loadingActividades">
                <i class="fa-solid fa-spinner fa-spin text-cyan me-1"></i> Cargando base de datos UNSPSC...
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
            <span>SECCIÓN 2 &mdash; Cronograma de la Convocatoria</span>
          </div>
          <span class="badge bg-light text-muted border fw-medium px-2 py-1">Paso 2 de 2</span>
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
              <input type="date" 
                     v-model="form.fecha_cierre" 
                     :min="form.fecha_inicio" 
                     class="form-control" 
                     :class="{ 'is-invalid': form.fecha_cierre && form.fecha_cierre < form.fecha_inicio }" 
                     required />
              <div class="invalid-feedback">No puede ser menor a fecha inicio.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Hora de Cierre (24h) <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_cierre" class="form-control" required />
            </div>
          </div>
        </div>
      </div>

      <!-- Barra de Acciones Minimalista -->
      <div class="card card-custom">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
          <div class="text-muted small d-flex align-items-center gap-2">
            <span class="text-cyan"><i class="fa-solid fa-shield-check"></i></span>
            <span>El consecutivo correlativo anual (ej. <code>PO-0001-26</code>) se generará automáticamente.</span>
          </div>

          <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
            <router-link to="/ofertas" class="btn btn-outline-minimal px-4">Cancelar</router-link>
            
            <button type="submit" 
                    class="btn btn-suplos px-4 d-flex align-items-center gap-2 shadow-none"
                    :disabled="!isFormValido || guardando">
              <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-check"></i>
              <span>{{ guardando ? 'Guardando...' : 'Guardar Oferta' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';

const router = useRouter();

const form = ref({
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
const loadingActividades = ref(true);
const guardando = ref(false);

const isObjetoValido = computed(() => {
  const val = form.value.objeto.trim();
  return val.length > 0 && val.length <= 150;
});

const isDescripcionValida = computed(() => {
  const val = form.value.descripcion.trim();
  return val.length > 0 && val.length <= 400;
});

const isPresupuestoValido = computed(() => {
  const val = String(form.value.presupuesto).trim();
  if (!val || !/^\d+(\.\d{1,2})?$/.test(val)) return false;
  return parseFloat(val) > 0;
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

const isActividadValida = computed(() => {
  return form.value.actividad_id !== '' && form.value.actividad_id !== null;
});

const errorCronograma = computed(() => {
  const { fecha_inicio, hora_inicio, fecha_cierre, hora_cierre } = form.value;
  if (!fecha_inicio || !hora_inicio || !fecha_cierre || !hora_cierre) return null;

  if (fecha_cierre < fecha_inicio) {
    return 'La fecha de cierre no puede ser menor a la fecha de inicio.';
  }
  if (fecha_inicio === fecha_cierre && hora_cierre <= hora_inicio) {
    return 'Si las fechas coinciden, la hora de cierre debe ser mayor a la de inicio.';
  }
  return null;
});

const isCronogramaValido = computed(() => {
  const { fecha_inicio, hora_inicio, fecha_cierre, hora_cierre } = form.value;
  return Boolean(fecha_inicio && hora_inicio && fecha_cierre && hora_cierre && !errorCronograma.value);
});

const isFormValido = computed(() => {
  return (
    isObjetoValido.value &&
    isDescripcionValida.value &&
    isPresupuestoValido.value &&
    isActividadValida.value &&
    isCronogramaValido.value
  );
});

const cargarActividades = async () => {
  loadingActividades.value = true;
  try {
    const res = await api.get('/actividades');
    if (res.data.success) {
      actividades.value = res.data.data;
    }
  } catch (err) {
    console.error('Error al cargar actividades:', err);
  } finally {
    loadingActividades.value = false;
  }
};

const guardarOferta = async () => {
  if (!isFormValido.value) return;

  guardando.value = true;
  try {
    const res = await api.post('/ofertas', form.value);
    if (res.data.success) {
      const nueva = res.data.data;
      await Swal.fire({
        icon: 'success',
        title: 'Oferta Creada Exitosamente',
        html: `Consecutivo Asignado: <span class="badge-consecutivo fs-6 mt-2">${nueva.consecutivo}</span>`,
        confirmButtonColor: '#0b2853',
        confirmButtonText: 'Ver Detalle de la Oferta',
      });
      router.push(`/ofertas/detalle/${nueva.id}`);
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error de validación',
      text: err.response?.data?.message || 'Error al guardar la oferta.',
      confirmButtonColor: '#0b2853',
    });
  } finally {
    guardando.value = false;
  }
};

onMounted(() => {
  cargarActividades();
});
</script>
