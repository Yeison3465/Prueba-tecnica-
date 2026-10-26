import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Evalúa si una oferta ha superado su fecha y hora límite de cierre.
 * @param {Object} oferta 
 * @param {Date} refDate 
 * @returns {boolean}
 */
export const verificarOfertaExpirada = (oferta, refDate = new Date()) => {
  if (!oferta || !oferta.fecha_cierre || !oferta.hora_cierre) return false;
  const fecha = String(oferta.fecha_cierre).split('T')[0];
  const hora = oferta.hora_cierre.length === 5 ? `${oferta.hora_cierre}:00` : oferta.hora_cierre.substring(0, 8);
  const limite = new Date(`${fecha}T${hora}`);
  return !isNaN(limite) && refDate >= limite;
};

/**
 * Retorna 'Cerrada' si la oferta estaba 'Publicada' y su cronograma ya venció,
 * o su estado original en cualquier otro caso.
 * @param {Object} oferta 
 * @param {Date} refDate 
 * @returns {string}
 */
export const resolverEstadoOferta = (oferta, refDate = new Date()) => {
  if (!oferta) return '';
  if (oferta.estado === 'Publicada' && verificarOfertaExpirada(oferta, refDate)) {
    return 'Cerrada';
  }
  return oferta.estado || '';
};

/**
 * Composable que provee reactividad en tiempo real (temporizador continuo)
 * para transicionar ofertas a 'Cerrada' en el segundo exacto de expiración.
 * @param {number} intervalMs Intervalo de comprobación en milisegundos (por defecto 3000ms)
 */
export function useOfertaEstado(intervalMs = 3000) {
  const ahora = ref(new Date());
  let timer = null;

  onMounted(() => {
    timer = setInterval(() => {
      ahora.value = new Date();
    }, intervalMs);
  });

  onUnmounted(() => {
    if (timer) clearInterval(timer);
  });

  const obtenerEstado = (oferta) => {
    return resolverEstadoOferta(oferta, ahora.value);
  };

  const estaCerrada = (oferta) => {
    if (!oferta) return false;
    return resolverEstadoOferta(oferta, ahora.value) === 'Cerrada';
  };

  return {
    ahora,
    obtenerEstado,
    estaCerrada,
  };
}
