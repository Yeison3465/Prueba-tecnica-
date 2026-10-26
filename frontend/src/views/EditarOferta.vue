<template>
  <div>
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><router-link to="/ofertas" class="text-decoration-none">Licitaciones</router-link></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Oferta</li>
          </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
          <h1 class="h4 fw-bold text-dark mb-0">Editar Oferta</h1>
          <span class="badge bg-secondary" v-if="form.consecutivo">{{ form.consecutivo }}</span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <router-link :to="'/ofertas/detalle/' + id" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
          <i class="fa-solid fa-eye"></i>
          <span>Ver Detalle</span>
        </router-link>
        <router-link to="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Volver al Listado</span>
        </router-link>
      </div>
    </div>

    <!-- Spinner de carga -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-2 small">Cargando datos de la oferta...</p>
    </div>

    <form v-else @submit.prevent="onGuardar" novalidate>
      <!-- Alerta informativa si la oferta está cerrada -->
      <div v-if="form.estado === 'Cerrada'" class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2 px-3">
        <i class="fa-solid fa-lock"></i>
        <span class="small">
          Esta oferta se encuentra <strong>Cerrada</strong> por cronograma límite. Para reactivarla, actualice la fecha/hora de cierre y seleccione el estado "Publicada".
        </span>
      </div>

      <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-file-lines me-2 text-primary"></i> Información Básica
          </h6>
          <span class="badge bg-light text-secondary border">
            Consecutivo: {{ form.consecutivo }}
          </span>
        </div>
        
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">Objeto de la Oferta <span class="text-danger">*</span></label>
                <span class="badge bg-light text-secondary border small">
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
                <label class="form-label fw-semibold mb-0">Descripción / Alcance <span class="text-danger">*</span></label>
                <span class="badge bg-light text-secondary border small">
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

            <!-- Moneda, Presupuesto y Estado -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Moneda <span class="text-danger">*</span></label>
              <select v-model="form.moneda" class="form-select" required>
                <option value="COP">COP (Peso Colombiano)</option>
                <option value="USD">USD (Dólar)</option>
                <option value="EUR">EUR (Euro)</option>
              </select>
            </div>

            <div class="col-md-6">
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
                       required />
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Estado</label>
              <select v-model="form.estado" class="form-select">
                <option value="Publicada">Publicada</option>
                <option value="Borrador">Borrador</option>
                <option value="Cerrada">Cerrada</option>
                <option value="Cancelada">Cancelada</option>
              </select>
            </div>

            <!-- Actividad UNSPSC -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Actividad (Catálogo UNSPSC) <span class="text-danger">*</span>
              </label>
              <BuscadorActividad 
                v-model="form.actividad_id" 
                :actividad-inicial="actividadInicial"
                :disabled="guardando"
                placeholder="Buscar por nombre o código UNSPSC..."
              />
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: CRONOGRAMA -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-calendar-days me-2 text-primary"></i> Cronograma
          </h6>
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
              <input type="date" v-model="form.fecha_cierre" :min="form.fecha_inicio" class="form-control" required />
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Hora Cierre <span class="text-danger">*</span></label>
              <input type="time" v-model="form.hora_cierre" class="form-control" required />
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 3: DOCUMENTOS (COMPONENTE REUTILIZABLE) -->
      <GestionDocumentos :oferta-id="id" v-model="documentos" />

      <!-- Barra de acciones -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
          <div class="small">
            <span v-if="documentos.length === 0" class="text-danger fw-semibold d-flex align-items-center gap-1">
              <i class="fa-solid fa-circle-exclamation"></i> Adjunte al menos un documento para habilitar el guardado.
            </span>
            <span v-else class="text-success fw-semibold d-flex align-items-center gap-1">
              <i class="fa-solid fa-circle-check"></i> Requisito cumplido ({{ documentos.length }} documento(s) adjunto(s)).
            </span>
          </div>

          <div class="d-flex gap-2">
            <router-link to="/ofertas" class="btn btn-secondary px-4">Cancelar</router-link>
            
            <button type="submit" 
                    class="btn btn-primary px-4 d-flex align-items-center gap-2"
                    :disabled="!isFormValido || guardando">
              <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-floppy-disk"></i>
              <span>{{ guardando ? 'Guardando...' : 'Guardar Cambios' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import BuscadorActividad from '../components/BuscadorActividad.vue';
import GestionDocumentos from '../components/GestionDocumentos.vue';
import { useOfertaEdit } from '../composables/useOfertaEdit';
import { usePresupuestoInput } from '../composables/usePresupuestoInput';
import { validarObjeto, validarDescripcion, validarPresupuesto, validarActividad, validarErrorCronograma } from '../utils/validators';

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
});

const { form, actividadInicial, documentos, loading, guardando, cargarDatos, actualizarOferta } = useOfertaEdit(props.id);
const { onPresupuestoKeydown, onPresupuestoInput, onPresupuestoPaste } = usePresupuestoInput(form);

// Reglas de validación reactivas
const isObjetoValido = computed(() => validarObjeto(form.value.objeto));
const isDescripcionValida = computed(() => validarDescripcion(form.value.descripcion));
const isPresupuestoValido = computed(() => validarPresupuesto(form.value.presupuesto));
const isActividadValida = computed(() => validarActividad(form.value.actividad_id));
const errorCronograma = computed(() => validarErrorCronograma(form.value));

const isFormValido = computed(() => {
  return (
    isObjetoValido.value &&
    isDescripcionValida.value &&
    isPresupuestoValido.value &&
    isActividadValida.value &&
    !errorCronograma.value &&
    documentos.value.length >= 1
  );
});

const onGuardar = () => {
  if (isFormValido.value) {
    actualizarOferta();
  }
};

onMounted(() => {
  cargarDatos();
});
</script>
