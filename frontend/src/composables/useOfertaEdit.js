import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import Swal from 'sweetalert2';

/**
 * Composable que gestiona la carga y actualización de una oferta existente.
 * @param {number|string} ofertaId ID de la oferta a editar
 */
export function useOfertaEdit(ofertaId) {
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

  const actividadInicial = ref(null);
  const documentos = ref([]);
  const loading = ref(true);
  const guardando = ref(false);

  const cargarDatos = async () => {
    loading.value = true;
    try {
      const res = await api.get(`/ofertas/${ofertaId}`);
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
        confirmButtonColor: '#0d6efd',
      }).then(() => router.push('/ofertas'));
    } finally {
      loading.value = false;
    }
  };

  const actualizarOferta = async () => {
    guardando.value = true;
    try {
      const res = await api.post(`/ofertas/${ofertaId}`, form.value);
      if (res.data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Actualización Exitosa',
          text: 'La oferta y sus documentos se guardaron correctamente.',
          confirmButtonColor: '#0d6efd',
          timer: 1500,
          showConfirmButton: false,
        });
        router.push(`/ofertas/detalle/${ofertaId}`);
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error al actualizar',
        text: err.response?.data?.message || 'Error al actualizar la oferta.',
        confirmButtonColor: '#0d6efd',
      });
    } finally {
      guardando.value = false;
    }
  };

  return {
    form,
    actividadInicial,
    documentos,
    loading,
    guardando,
    cargarDatos,
    actualizarOferta,
  };
}
