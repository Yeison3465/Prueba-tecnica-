<template>
  <div>
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none">Licitaciones</router-link></li>
            <li class="breadcrumb-item active" aria-current="page">Nueva Oferta</li>
          </ol>
        </nav>
        <h1 class="h4 fw-bold text-dark mb-0">Crear Nueva Oferta</h1>
      </div>
      <div>
        <router-link to="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Volver al Listado</span>
        </router-link>
      </div>
    </div>

    <form @submit.prevent="guardarOferta" novalidate>
      <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-file-lines me-2 text-primary"></i> Información Básica
          </h6>
          <span class="badge bg-light text-secondary border">Paso 1 de 2</span>
        </div>
        
        <div class="card-body p-4">
          <div class="row g-3">
            <!-- Objeto -->
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">
                  Objeto de la Oferta <span class="text-danger">*</span>
                </label>
                <span class="badge bg-light text-secondary border small">
                  {{ form.objeto.length }} / 150
                </span>
              </div>
              <input type="text" 
                     v-model="form.objeto" 
                     maxlength="150" 
                     class="form-control" 
                     :class="{ 'is-invalid': form.objeto.length > 0 && !isObjetoValido }"
                     placeholder="Ej. Adquisición e implementación de licencias..." 
                     required />
              <div class="invalid-feedback">
                El objeto es obligatorio y no puede superar 150 caracteres.
              </div>
            </div>

            <!-- Descripción -->
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">
                  Descripción / Alcance <span class="text-danger">*</span>
                </label>
                <span class="badge bg-light text-secondary border small">
                  {{ form.descripcion.length }} / 400
                </span>
              </div>
              <textarea v-model="form.descripcion" 
                        maxlength="400" 
                        rows="3" 
                        class="form-control" 
                        :class="{ 'is-invalid': form.descripcion.length > 0 && !isDescripcionValida }"
                        placeholder="Detalle de especificaciones técnicas y alcance..." 
                        required></textarea>
              <div class="invalid-feedback">
                La descripción es obligatoria y no puede superar 400 caracteres.
              </div>
            </div>

            <!-- Moneda y Presupuesto -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Moneda <span class="text-danger">*</span></label>
              <select v-model="form.moneda" class="form-select" required>
                <option value="COP">COP (Peso Colombiano)</option>
                <option value="USD">USD (Dólar)</option>
                <option value="EUR">EUR (Euro)</option>
              </select>
            </div>

            <div class="col-md-8">
              <label class="form-label fw-semibold">Presupuesto Estimado <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light fw-bold">{{ form.moneda }}</span>
                <input type="text" 
                       v-model="form.presupuesto" 
                       class="form-control" 
                       :class="{ 'is-invalid': form.presupuesto !== '' && !isPresupuestoValido }"
                       @keydown="onPresupuestoKeydown"
                       @input="onPresupuestoInput"
                       @paste="onPresupuestoPaste"
                       placeholder="0.00" 
                       required />
              </div>
              <div class="invalid-feedback d-block small" v-if="form.presupuesto !== '' && !isPresupuestoValido">
                Debe ser un número mayor a 0 con máximo dos decimales (ej. 1500000.50).
              </div>
            </div>

            <!-- Actividad UNSPSC -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Actividad (Catálogo UNSPSC) <span class="text-danger">*</span>
              </label>
              <BuscadorActividad 
                v-model="form.actividad_id" 
                :disabled="guardando"
                placeholder="Buscar actividad o código UNSPSC..."
              />
              <div class="form-text small text-muted">
                Busque y seleccione la clasificación de bien/servicio UNSPSC correspondiente.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: CRONOGRAMA -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-calendar-days me-2 text-primary"></i> Cronograma de la Convocatoria
          </h6>
          <span class="badge bg-light text-secondary border">Paso 2 de 2</span>
        </div>

        <div class="card-body p-4">
          <div v-if="errorCronograma" class="alert alert-warning d-flex align-items-center gap-2 mb-3 py-2 px-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="small fw-semibold">{{ errorCronograma }}</span>
          </div>

          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Fecha Inicio <span class="text-danger">*</span></label>
              <input type="date" v-model="form.fecha_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Hora Inicio <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Fecha Cierre <span class="text-danger">*</span></label>
              <input type="date" 
                     v-model="form.fecha_cierre" 
                     :min="form.fecha_inicio" 
                     class="form-control" 
                     :class="{ 'is-invalid': form.fecha_cierre && form.fecha_cierre < form.fecha_inicio }" 
                     required />
              <div class="invalid-feedback">No puede ser menor a fecha inicio.</div>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Hora Cierre <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_cierre" class="form-control" required />
            </div>
          </div>
        </div>
      </div>

      <!-- Barra de Acciones -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-end gap-2 p-3">
          <router-link to="/ofertas" class="btn btn-secondary px-4">Cancelar</router-link>
          
          <button type="submit" 
                  class="btn btn-primary px-4 d-flex align-items-center gap-2"
                  :disabled="!isFormValido || guardando">
            <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
            <i v-else class="fa-solid fa-check"></i>
            <span>{{ guardando ? 'Guardando...' : 'Guardar Oferta' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';
import BuscadorActividad from '../components/BuscadorActividad.vue';
import { validarObjeto, validarDescripcion, validarPresupuesto, validarActividad, validarErrorCronograma } from '../utils/validators';
import { usePresupuestoInput } from '../composables/usePresupuestoInput';

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

const guardando = ref(false);

// Control de entrada numérica de presupuesto
const { onPresupuestoKeydown, onPresupuestoInput, onPresupuestoPaste } = usePresupuestoInput(form);

// Reglas de validación reactivas
const isObjetoValido = computed(() => validarObjeto(form.value.objeto));
const isDescripcionValida = computed(() => validarDescripcion(form.value.descripcion));
const isPresupuestoValido = computed(() => validarPresupuesto(form.value.presupuesto));
const isActividadValida = computed(() => validarActividad(form.value.actividad_id));
const errorCronograma = computed(() => validarErrorCronograma(form.value));

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
        html: `Consecutivo Asignado: <b>${nueva.consecutivo}</b>`,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Ver Detalle de la Oferta',
      });
      router.push(`/ofertas/detalle/${nueva.id}`);
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error de validación',
      text: err.response?.data?.message || 'Error al guardar la oferta.',
      confirmButtonColor: '#0d6efd',
    });
  } finally {
    guardando.value = false;
  }
};
</script>
