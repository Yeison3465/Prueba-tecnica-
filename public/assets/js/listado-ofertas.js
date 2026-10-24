const { createApp, ref, onMounted, watch } = Vue;

createApp({
    setup() {
        const ofertas = ref([]);
        const loading = ref(false);
        const pagination = ref({
            total: 0,
            per_page: 10,
            current_page: 1,
            last_page: 1
        });

        // Filtros según el PDF: Consecutivo, Objeto, Descripción
        const filters = ref({
            consecutivo: '',
            objeto: '',
            descripcion: ''
        });

        let searchTimeout = null;

        const cargarOfertas = async (page = 1) => {
            loading.value = true;
            try {
                const params = {
                    page: page,
                    per_page: pagination.value.per_page,
                    consecutivo: filters.value.consecutivo,
                    objeto: filters.value.objeto,
                    descripcion: filters.value.descripcion
                };

                const response = await axios.get('/api/ofertas', { params });
                if (response.data.success) {
                    ofertas.value = response.data.data;
                    pagination.value = response.data.pagination;
                }
            } catch (error) {
                console.error("Error al cargar ofertas:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de carga',
                    text: 'No se pudieron obtener las ofertas desde el servidor.'
                });
            } finally {
                loading.value = false;
            }
        };

        const cambiarPagina = (page) => {
            if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
                return;
            }
            cargarOfertas(page);
        };

        const limpiarFiltros = () => {
            filters.value.consecutivo = '';
            filters.value.objeto = '';
            filters.value.descripcion = '';
            cargarOfertas(1);
        };

        const exportarExcel = () => {
            const params = new URLSearchParams({
                consecutivo: filters.value.consecutivo,
                objeto: filters.value.objeto,
                descripcion: filters.value.descripcion
            });
            window.location.href = `/ofertas/exportar-excel?${params.toString()}`;
        };

        const formatearMoneda = (valor, moneda = 'COP') => {
            if (!valor) return '$ 0.00';
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: moneda,
                minimumFractionDigits: 2
            }).format(valor);
        };

        const formatearFecha = (fechaStr) => {
            if (!fechaStr) return '';
            // fechaStr puede venir como YYYY-MM-DD
            return fechaStr.split('T')[0];
        };

        // Observar cambios en filtros con debounce
        watch(filters, () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                cargarOfertas(1);
            }, 300);
        }, { deep: true });

        onMounted(() => {
            cargarOfertas(1);
        });

        return {
            ofertas,
            loading,
            pagination,
            filters,
            cargarOfertas,
            cambiarPagina,
            limpiarFiltros,
            exportarExcel,
            formatearMoneda,
            formatearFecha
        };
    }
}).mount('#app-listado');
