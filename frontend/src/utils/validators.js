/**
 * Reglas de validación para ofertas y convocatorias en el frontend.
 */

/**
 * Valida el objeto de la oferta: obligatorio, no solo espacios, máx 150 caracteres.
 * @param {string} val 
 * @returns {boolean}
 */
export const validarObjeto = (val) => {
  if (!val || typeof val !== 'string') return false;
  const trimmed = val.trim();
  return trimmed.length > 0 && val.length <= 150;
};

/**
 * Valida la descripción de la oferta: obligatoria, no solo espacios, máx 400 caracteres.
 * @param {string} val 
 * @returns {boolean}
 */
export const validarDescripcion = (val) => {
  if (!val || typeof val !== 'string') return false;
  const trimmed = val.trim();
  return trimmed.length > 0 && val.length <= 400;
};

/**
 * Valida el presupuesto: numérico positivo mayor a 0 con hasta 2 decimales.
 * @param {number|string} val 
 * @returns {boolean}
 */
export const validarPresupuesto = (val) => {
  const str = String(val ?? '').trim();
  if (!str) return false;
  if (!/^\d+(\.\d{1,2})?$/.test(str)) return false;
  return parseFloat(str) > 0;
};

/**
 * Valida que se haya seleccionado una actividad del clasificador UNSPSC.
 * @param {number|string} val 
 * @returns {boolean}
 */
export const validarActividad = (val) => {
  return val !== '' && val !== null && val !== undefined;
};

/**
 * Valida la consistencia cronológica de una oferta.
 * @param {Object} params
 * @param {string} params.fecha_inicio
 * @param {string} params.hora_inicio
 * @param {string} params.fecha_cierre
 * @param {string} params.hora_cierre
 * @param {string} [params.estado]
 * @returns {string|null} Mensaje de error o null si es válido
 */
export const validarErrorCronograma = ({ fecha_inicio, hora_inicio, fecha_cierre, hora_cierre, estado = 'Publicada' }) => {
  if (!fecha_inicio || !hora_inicio || !fecha_cierre || !hora_cierre) {
    return null;
  }

  if (fecha_cierre < fecha_inicio) {
    return 'La fecha de cierre no puede ser menor a la fecha de inicio.';
  }

  if (fecha_inicio === fecha_cierre && hora_cierre <= hora_inicio) {
    return 'Si las fechas coinciden, la hora de cierre debe ser mayor a la de inicio.';
  }

  if (estado === 'Publicada') {
    const hora = hora_cierre.length === 5 ? `${hora_cierre}:00` : hora_cierre;
    const fechaHoraCierre = new Date(`${fecha_cierre}T${hora}`);
    if (!isNaN(fechaHoraCierre) && fechaHoraCierre <= new Date()) {
      return 'Para que la oferta esté en estado "Publicada", la fecha y hora de cierre deben ser posteriores al momento actual.';
    }
  }

  return null;
};
