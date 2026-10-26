import { ref } from 'vue';
import api from '../services/api';

/**
 * Composable para la búsqueda y selección de actividades UNSPSC.
 */
export function useActividadSearch() {
  const termino = ref('');
  const resultados = ref([]);
  const loading = ref(false);
  const actividadSeleccionada = ref(null);
  let debounceTimer = null;

  const buscarActividades = async (query = '') => {
    loading.value = true;
    try {
      const res = await api.get('/actividades', {
        params: { search: query.trim() },
      });
      if (res.data.success) {
        resultados.value = res.data.data;
      }
    } catch (err) {
      console.error('Error al buscar actividades:', err);
    } finally {
      loading.value = false;
    }
  };

  const debouncedBuscar = (delay = 250) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      buscarActividades(termino.value);
    }, delay);
  };

  const cargarPorId = async (id, actividadInicial = null) => {
    if (!id) {
      actividadSeleccionada.value = null;
      return;
    }
    if (actividadSeleccionada.value && actividadSeleccionada.value.id === parseInt(id)) {
      return;
    }
    if (actividadInicial && actividadInicial.id === parseInt(id)) {
      actividadSeleccionada.value = actividadInicial;
      return;
    }
    try {
      const res = await api.get(`/actividades/${id}`);
      if (res.data.success) {
        actividadSeleccionada.value = res.data.data;
      }
    } catch (err) {
      console.error('Error al cargar detalle de actividad:', err);
    }
  };

  const limpiar = () => {
    actividadSeleccionada.value = null;
    termino.value = '';
    resultados.value = [];
  };

  return {
    termino,
    resultados,
    loading,
    actividadSeleccionada,
    buscarActividades,
    debouncedBuscar,
    cargarPorId,
    limpiar,
  };
}
