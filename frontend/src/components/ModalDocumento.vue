<template>
  <div class="modal fade" id="modalDocVue" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" ref="modalRef">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
        <div class="modal-header bg-white border-bottom px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <span class="section-icon" style="width: 28px; height: 28px; font-size: 0.8rem;">
              <i class="fa-solid fa-file-circle-plus"></i>
            </span>
            <h5 class="modal-title fw-bold text-navy mb-0" id="modalLabel" style="font-size: 1.05rem;">
              Adjuntar Documento
            </h5>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        
        <form @submit.prevent="subirDocumento" novalidate>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label">Título del Documento <span class="text-danger">*</span></label>
              <input type="text" 
                     v-model="titulo" 
                     maxlength="100" 
                     class="form-control" 
                     placeholder="Ej. Términos de Referencia Técnicos" 
                     required />
              <div class="form-text small text-muted">Nombre descriptivo del documento (Máx 100 caracteres).</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Descripción Corta <span class="text-danger">*</span></label>
              <textarea v-model="descripcion" 
                        maxlength="200" 
                        rows="2" 
                        class="form-control" 
                        placeholder="Breve reseña del contenido del archivo adjunto..." 
                        required></textarea>
              <div class="form-text small text-muted">Alcance del documento (Máx 200 caracteres).</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Archivo Adjunto <span class="text-danger">*</span></label>
              <input type="file" 
                     ref="fileInputRef"
                     @change="handleFile" 
                     class="form-control" 
                     accept=".pdf,.zip" 
                     required />
              <div class="form-text small text-cyan fw-semibold mt-2">
                <i class="fa-solid fa-circle-info me-1"></i> Formatos admitidos: Solo <b>PDF</b> o <b>ZIP</b>.
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light px-4 py-3 border-top">
            <button type="button" class="btn btn-outline-minimal btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" 
                    class="btn btn-suplos btn-sm d-flex align-items-center gap-2 shadow-none" 
                    :disabled="!isValid || uploading">
              <i v-if="uploading" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-plus"></i>
              <span>{{ uploading ? 'Subiendo...' : 'Agregar al Listado' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '../services/api';
import Swal from 'sweetalert2';

const props = defineProps({
  ofertaId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits(['documento-agregado']);

const titulo = ref('');
const descripcion = ref('');
const archivo = ref(null);
const uploading = ref(false);
const fileInputRef = ref(null);

const isValid = computed(() => {
  return titulo.value.trim().length > 0 &&
         descripcion.value.trim().length > 0 &&
         archivo.value !== null;
});

const handleFile = (e) => {
  const files = e.target.files;
  if (!files.length) return;
  const file = files[0];
  const ext = file.name.split('.').pop().toLowerCase();

  if (!['pdf', 'zip'].includes(ext)) {
    Swal.fire({
      icon: 'warning',
      title: 'Formato no permitido',
      text: 'Solo se admiten documentos en formato PDF o comprimidos en ZIP.',
      confirmButtonColor: '#0b2853',
    });
    e.target.value = '';
    archivo.value = null;
    return;
  }

  archivo.value = file;
};

const subirDocumento = async () => {
  if (!isValid.value) return;

  uploading.value = true;
  const formData = new FormData();
  formData.append('titulo', titulo.value.trim());
  formData.append('descripcion', descripcion.value.trim());
  formData.append('archivo', archivo.value);

  try {
    const res = await api.post(`/ofertas/${props.ofertaId}/documentos`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data.success) {
      emit('documento-agregado', res.data.data);
      
      // Limpiar campos
      titulo.value = '';
      descripcion.value = '';
      archivo.value = null;
      if (fileInputRef.value) fileInputRef.value.value = '';

      // Cerrar modal
      const modalEl = document.getElementById('modalDocVue');
      const modal = window.bootstrap?.Modal?.getInstance(modalEl);
      if (modal) modal.hide();

      Swal.fire({
        icon: 'success',
        title: 'Documento Adjuntado',
        timer: 1500,
        showConfirmButton: false,
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error al subir',
      text: err.response?.data?.message || 'Error al procesar el archivo.',
      confirmButtonColor: '#0b2853',
    });
  } finally {
    uploading.value = false;
  }
};
</script>
