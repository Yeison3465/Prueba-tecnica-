const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        // Modo (crear o editar)
        const isEditMode = ref(window.OFFER_ID !== undefined && window.OFFER_ID !== null);
        const ofertaId = ref(window.OFFER_ID || null);

        // Estado del formulario
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
            estado: 'Publicada'
        });

        // Actividades UNSPSC
        const actividades = ref([]);
        const loadingActividades = ref(true);

        // Documentos (para el modo edición)
        const documentos = ref([]);
        const nuevoDoc = ref({
            titulo: '',
            descripcion: '',
            archivo: null,
            archivoNombre: ''
        });

        const guardando = ref(false);
        const subiendoDoc = ref(false);
        const errors = ref({});

        // =========================================================================
        // VALIDACIONES EN TIEMPO REAL
        // =========================================================================

        // 1. Objeto: no vacío/solo espacios, max 150
        const objetoLength = computed(() => (form.value.objeto || '').length);
        const isObjetoValido = computed(() => {
            const val = (form.value.objeto || '').trim();
            return val.length > 0 && val.length <= 150;
        });

        // 2. Descripción: no vacía/solo espacios, max 400
        const descripcionLength = computed(() => (form.value.descripcion || '').length);
        const isDescripcionValida = computed(() => {
            const val = (form.value.descripcion || '').trim();
            return val.length > 0 && val.length <= 400;
        });

        // 3. Moneda: COP, USD, EUR
        const isMonedaValida = computed(() => {
            return ['COP', 'USD', 'EUR'].includes(form.value.moneda);
        });

        // 4. Presupuesto: > 0, números y punto decimal, máx 2 decimales
        const isPresupuestoValido = computed(() => {
            const val = String(form.value.presupuesto || '').trim();
            if (val === '') return false;
            if (!/^\d+(\.\d{1,2})?$/.test(val)) return false;
            return parseFloat(val) > 0;
        });

        // 5. Actividad: Seleccionada y válida
        const isActividadValida = computed(() => {
            return form.value.actividad_id !== '' && form.value.actividad_id !== null;
        });

        // 6. Fechas y Horas (Cronograma)
        const errorCronograma = computed(() => {
            const { fecha_inicio, hora_inicio, fecha_cierre, hora_cierre } = form.value;

            if (!fecha_inicio) return 'La fecha de inicio es requerida.';
            if (!hora_inicio) return 'La hora de inicio es requerida.';
            if (!fecha_cierre) return 'La fecha de cierre es requerida.';
            if (!hora_cierre) return 'La hora de cierre es requerida.';

            if (fecha_cierre < fecha_inicio) {
                return 'La fecha de cierre no puede ser menor a la fecha de inicio.';
            }

            if (fecha_inicio === fecha_cierre && hora_cierre <= hora_inicio) {
                return 'Si la fecha de inicio y cierre son el mismo día, la hora de cierre debe ser mayor a la hora de inicio.';
            }

            return null; // Sin error
        });

        const isCronogramaValido = computed(() => errorCronograma.value === null);

        // 7. Validación de documentos en EDICIÓN: Debe existir al menos 1 documento
        const isDocumentosValido = computed(() => {
            if (!isEditMode.value) return true; // En creación no es obligatorio adjuntar aquí
            return documentos.value.length >= 1;
        });

        // =========================================================================
        // REGLA GENERAL: BOTÓN GUARDAR SOLO SE HABILITA SI TODO ES VÁLIDO
        // =========================================================================
        const isFormValido = computed(() => {
            return (
                isObjetoValido.value &&
                isDescripcionValida.value &&
                isMonedaValida.value &&
                isPresupuestoValido.value &&
                isActividadValida.value &&
                isCronogramaValido.value &&
                isDocumentosValido.value &&
                !guardando.value
            );
        });

        // =========================================================================
        // MÉTODOS Y LLAMADAS AJAX VÍA AXIOS
        // =========================================================================

        const cargarActividades = async () => {
            loadingActividades.value = true;
            try {
                const res = await axios.get('/api/actividades');
                if (res.data.success) {
                    actividades.value = res.data.data;
                }
            } catch (err) {
                console.error("Error al cargar actividades:", err);
            } finally {
                loadingActividades.value = false;
            }
        };

        const cargarDatosOferta = async () => {
            if (!isEditMode.value) return;

            try {
                const res = await axios.get(`/api/ofertas/${ofertaId.value}`);
                if (res.data.success) {
                    const data = res.data.data;
                    form.value.consecutivo  = data.consecutivo;
                    form.value.objeto       = data.objeto;
                    form.value.descripcion  = data.descripcion;
                    form.value.moneda       = data.moneda;
                    form.value.presupuesto  = data.presupuesto;
                    form.value.actividad_id = data.actividad_id;
                    form.value.fecha_inicio = data.fecha_inicio ? data.fecha_inicio.split('T')[0] : '';
                    form.value.hora_inicio  = data.hora_inicio ? data.hora_inicio.substring(0, 5) : '08:00';
                    form.value.fecha_cierre = data.fecha_cierre ? data.fecha_cierre.split('T')[0] : '';
                    form.value.hora_cierre  = data.hora_cierre ? data.hora_cierre.substring(0, 5) : '18:00';
                    form.value.estado       = data.estado;

                    documentos.value        = data.documentos || [];
                }
            } catch (err) {
                console.error("Error al cargar oferta:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Oferta no encontrada',
                    text: 'No se pudo cargar la información de la oferta.'
                }).then(() => {
                    window.location.href = '/ofertas';
                });
            }
        };

        const guardarOferta = async () => {
            if (!isFormValido.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulario incompleto',
                    text: 'Por favor complete todos los campos obligatorios correctamente.'
                });
                return;
            }

            guardando.value = true;
            errors.value = {};

            try {
                if (isEditMode.value) {
                    // Actualización
                    const res = await axios.post(`/api/ofertas/${ofertaId.value}`, form.value);
                    if (res.data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Actualización Exitosa!',
                            text: 'La oferta ha sido actualizada correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.location.href = `/ofertas/detalle/${ofertaId.value}`;
                    }
                } else {
                    // Creación
                    const res = await axios.post('/api/ofertas', form.value);
                    if (res.data.success) {
                        const nuevaOferta = res.data.data;
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Oferta Creada!',
                            html: `Oferta registrada exitosamente con el consecutivo: <b>${nuevaOferta.consecutivo}</b>`,
                            confirmButtonText: 'Ver Detalle'
                        });
                        window.location.href = `/ofertas/detalle/${nuevaOferta.id}`;
                    }
                }
            } catch (err) {
                console.error("Error al guardar oferta:", err);
                if (err.response && err.response.data && err.response.data.errors) {
                    errors.value = err.response.data.errors;
                    const errorMsgs = Object.values(errors.value).join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errorMsgs
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (err.response && err.response.data && err.response.data.message) || 'Error al procesar la solicitud en el servidor.'
                    });
                }
            } finally {
                guardando.value = false;
            }
        };

        // =========================================================================
        // GESTIÓN DE DOCUMENTOS (EDICIÓN)
        // =========================================================================
        const onFileChange = (e) => {
            const files = e.target.files;
            if (!files.length) return;
            const file = files[0];
            const ext = file.name.split('.').pop().toLowerCase();

            if (!['pdf', 'zip'].includes(ext)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato no permitido',
                    text: 'Solo se permiten archivos en formato PDF o ZIP.'
                });
                e.target.value = '';
                nuevoDoc.value.archivo = null;
                nuevoDoc.value.archivoNombre = '';
                return;
            }

            nuevoDoc.value.archivo = file;
            nuevoDoc.value.archivoNombre = file.name;
        };

        const isNuevoDocValido = computed(() => {
            return (
                nuevoDoc.value.titulo.trim().length > 0 &&
                nuevoDoc.value.descripcion.trim().length > 0 &&
                nuevoDoc.value.archivo !== null &&
                !subiendoDoc.value
            );
        });

        const agregarDocumento = async () => {
            if (!isNuevoDocValido.value) return;

            subiendoDoc.value = true;
            const formData = new FormData();
            formData.append('titulo', nuevoDoc.value.titulo.trim());
            formData.append('descripcion', nuevoDoc.value.descripcion.trim());
            formData.append('archivo', nuevoDoc.value.archivo);

            try {
                const res = await axios.post(`/api/ofertas/${ofertaId.value}/documentos`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (res.data.success) {
                    documentos.value.push(res.data.data);

                    // Limpiar modal
                    nuevoDoc.value.titulo = '';
                    nuevoDoc.value.descripcion = '';
                    nuevoDoc.value.archivo = null;
                    nuevoDoc.value.archivoNombre = '';
                    const fileInput = document.getElementById('modalFileInput');
                    if (fileInput) fileInput.value = '';

                    // Cerrar modal de bootstrap
                    const modalEl = document.getElementById('modalAgregarDocumento');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Documento agregado',
                        text: 'El documento se adjuntó correctamente.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                console.error("Error al subir documento:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al subir',
                    text: (err.response && err.response.data && err.response.data.message) || 'No se pudo subir el archivo.'
                });
            } finally {
                subiendoDoc.value = false;
            }
        };

        const eliminarDocumento = async (docId) => {
            const confirm = await Swal.fire({
                title: '¿Eliminar documento?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirm.isConfirmed) return;

            try {
                const res = await axios.delete(`/api/ofertas/${ofertaId.value}/documentos/${docId}`);
                if (res.data.success) {
                    documentos.value = documentos.value.filter(d => d.id !== docId);
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'El documento ha sido eliminado.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                console.error("Error al eliminar documento:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar el documento.'
                });
            }
        };

        onMounted(async () => {
            await cargarActividades();
            if (isEditMode.value) {
                await cargarDatosOferta();
            }
        });

        return {
            isEditMode,
            ofertaId,
            form,
            actividades,
            loadingActividades,
            documentos,
            nuevoDoc,
            guardando,
            subiendoDoc,
            errors,
            objetoLength,
            isObjetoValido,
            descripcionLength,
            isDescripcionValida,
            isMonedaValida,
            isPresupuestoValido,
            isActividadValida,
            errorCronograma,
            isCronogramaValido,
            isDocumentosValido,
            isFormValido,
            guardarOferta,
            onFileChange,
            isNuevoDocValido,
            agregarDocumento,
            eliminarDocumento
        };
    }
}).mount('#app-form-oferta');
