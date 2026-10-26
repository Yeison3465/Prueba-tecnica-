<template>
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-paperclip me-2 text-primary"></i> Documentos de la Oferta
      </h6>
      <button type="button" 
              class="btn btn-sm btn-primary d-flex align-items-center gap-1" 
              data-bs-toggle="modal" 
              data-bs-target="#modalDocVue">
        <i class="fa-solid fa-plus"></i>
        <span>Agregar Documento</span>
      </button>
    </div>

    <div class="card-body p-4">
      <!-- Alerta si no hay documentos (Regla obligatoria) -->
      <div v-if="!modelValue || modelValue.length === 0" class="alert alert-warning d-flex align-items-center gap-2 mb-3">
        <i class="fa-solid fa-circle-exclamation text-danger fa-lg"></i>
        <span class="small fw-semibold text-danger">
          Regla obligatoria: Debe existir al menos un (1) documento adjunto (PDF o ZIP) para poder guardar los cambios.
        </span>
      </div>

      <!-- Tabla de documentos Bootstrap -->
      <div v-if="modelValue && modelValue.length > 0" class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Título</th>
              <th>Descripción</th>
              <th>Formato</th>
              <th>Fecha Registro</th>
              <th class="text-center pe-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="doc in modelValue" :key="doc.id">
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
                <div class="d-flex justify-content-center gap-1">
                  <a :href="'/ofertas/' + ofertaId + '/documentos/' + doc.id + '/descargar'" 
                     target="_blank" 
                     class="btn btn-sm btn-outline-primary"
                     title="Descargar archivo">
                    <i class="fa-solid fa-download"></i>
                  </a>
                  <button type="button" 
                          @click="eliminarDocumento(doc.id)" 
                          class="btn btn-sm btn-outline-danger" 
                          title="Eliminar documento">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío -->
      <div v-else class="text-center py-4 text-muted">
        <p class="mb-2 small">Aún no se ha cargado ningún documento adjunto.</p>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDocVue">
          <i class="fa-solid fa-upload me-1"></i> Cargar documento obligatorio
        </button>
      </div>
    </div>

    <!-- Modal para adjuntar nuevo documento -->
    <ModalDocumento :oferta-id="ofertaId" @documento-agregado="onDocumentoAgregado" />
  </div>
</template>

<script setup>
import ModalDocumento from './ModalDocumento.vue';
import { formatearFechaHora } from '../utils/formatters';
import api from '../services/api';
import Swal from 'sweetalert2';

const props = defineProps({
  ofertaId: {
    type: [Number, String],
    required: true,
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update:modelValue']);

const onDocumentoAgregado = (nuevoDoc) => {
  const actualizados = [...props.modelValue, nuevoDoc];
  emit('update:modelValue', actualizados);
};

const eliminarDocumento = async (docId) => {
  const confirm = await Swal.fire({
    title: '¿Eliminar documento?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  });

  if (!confirm.isConfirmed) return;

  try {
    const res = await api.delete(`/ofertas/${props.ofertaId}/documentos/${docId}`);
    if (res.data.success) {
      const actualizados = props.modelValue.filter(d => d.id !== docId);
      emit('update:modelValue', actualizados);
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
      confirmButtonColor: '#0d6efd',
    });
  }
};
</script>
