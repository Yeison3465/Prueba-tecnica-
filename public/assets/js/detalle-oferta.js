const { createApp, ref, onMounted } = Vue;

createApp({
    setup() {
        const ofertaId = window.OFFER_ID;
        const oferta = ref(null);
        const loading = ref(true);

        const cargarDetalle = async () => {
            loading.value = true;
            try {
                const res = await axios.get(`/api/ofertas/${ofertaId}`);
                if (res.data.success) {
                    oferta.value = res.data.data;
                }
            } catch (err) {
                console.error("Error al cargar detalle de oferta:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrada',
                    text: 'No se encontró la oferta solicitada.'
                }).then(() => {
                    window.location.href = '/ofertas';
                });
            } finally {
                loading.value = false;
            }
        };

        const formatearMoneda = (val, moneda = 'COP') => {
            if (!val) return '$ 0.00';
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: moneda,
                minimumFractionDigits: 2
            }).format(val);
        };

        const formatearFecha = (str) => {
            if (!str) return 'N/A';
            return str.split('T')[0];
        };

        onMounted(() => {
            cargarDetalle();
        });

        return {
            ofertaId,
            oferta,
            loading,
            formatearMoneda,
            formatearFecha
        };
    }
}).mount('#app-detalle-oferta');
