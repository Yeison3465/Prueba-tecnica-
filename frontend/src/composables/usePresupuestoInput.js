/**
 * Composable para control y filtrado numérico del campo de presupuesto.
 * Limita a dígitos, un solo separador decimal y máximo 2 decimales.
 */
export function usePresupuestoInput(formRef) {
  const sanitizeNumber = (str) => {
    let val = String(str || '').replace(',', '.').replace(/[^0-9.]/g, '');
    const parts = val.split('.');
    if (parts.length > 2) {
      val = parts[0] + '.' + parts.slice(1).join('');
    }
    if (parts.length === 2 && parts[1].length > 2) {
      val = parts[0] + '.' + parts[1].substring(0, 2);
    }
    return val;
  };

  const onPresupuestoKeydown = (event) => {
    const allowedKeys = [
      'Backspace', 'Tab', 'Enter', 'Escape', 'Delete',
      'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'
    ];

    if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) {
      return;
    }

    if (event.key === '.' || event.key === ',') {
      const currentVal = String(formRef.value.presupuesto || '');
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
    formRef.value.presupuesto = sanitizeNumber(event.target.value);
  };

  const onPresupuestoPaste = (event) => {
    event.preventDefault();
    const pasted = (event.clipboardData || window.clipboardData)?.getData('text') || '';
    formRef.value.presupuesto = sanitizeNumber(pasted);
  };

  return {
    onPresupuestoKeydown,
    onPresupuestoInput,
    onPresupuestoPaste,
  };
}
