/**
 * Utilidades de formateo para el frontend de Licitaciones y Ofertas.
 */

/**
 * Formatea un valor numérico como moneda internacional (COP, USD, EUR).
 * @param {number|string} val 
 * @param {string} moneda 
 * @returns {string}
 */
export const formatearMoneda = (val, moneda = 'COP') => {
  if (val === null || val === undefined || val === '') return '$ 0,00';
  const num = typeof val === 'string' ? parseFloat(val) : val;
  if (isNaN(num)) return '$ 0,00';

  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: moneda || 'COP',
      minimumFractionDigits: 2,
    }).format(num);
  } catch (e) {
    return `${moneda} ${num.toFixed(2)}`;
  }
};

/**
 * Extrae y formatea la fecha base (YYYY-MM-DD) desde strings ISO o datetime SQL.
 * @param {string} str 
 * @returns {string}
 */
export const formatearFecha = (str) => {
  if (!str) return 'N/A';
  return str.split('T')[0].split(' ')[0];
};

/**
 * Formatea fecha y hora combinadas (YYYY-MM-DD HH:mm).
 * @param {string} str 
 * @returns {string}
 */
export const formatearFechaHora = (str) => {
  if (!str) return 'N/A';
  const parts = str.split('T');
  if (parts.length === 2) {
    const hora = parts[1].substring(0, 5);
    return `${parts[0]} ${hora}`;
  }
  return str.substring(0, 16);
};

/**
 * Normaliza la hora en formato 24h (HH:mm).
 * @param {string} str 
 * @returns {string}
 */
export const formatearHora = (str) => {
  if (!str) return '--:--';
  return str.substring(0, 5);
};
