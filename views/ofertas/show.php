<?php require __DIR__ . '/../layouts/header.php'; ?>

<div id="app-detalle-oferta" v-cloak>
    <!-- Loading state -->
    <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando detalle...</span>
        </div>
        <p class="text-muted mt-2">Cargando información consolidada de la oferta...</p>
    </div>

    <div v-else-if="oferta">
        <!-- Navegación y Acciones -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="/ofertas" class="text-decoration-none text-muted">Ofertas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ oferta.consecutivo }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="h3 fw-bold text-dark mb-0">{{ oferta.objeto }}</h1>
                    <span class="badge-consecutivo fs-6">{{ oferta.consecutivo }}</span>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <!-- Botón para acceder al formulario de edición (REQUERIDO POR EL PDF) -->
                <a :href="'/ofertas/editar/' + oferta.id" class="btn btn-suplos d-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Editar Oferta</span>
                </a>
                <a href="/ofertas" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>

        <!-- Tarjeta Principal con Pestañas (Tabs) -->
        <div class="card card-custom">
            <!-- Encabezado de Pestañas -->
            <div class="card-header bg-white border-bottom pt-3 px-4">
                <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" id="ofertaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center gap-2" 
                                id="info-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#info-pane" 
                                type="button" 
                                role="tab" 
                                aria-controls="info-pane" 
                                aria-selected="true">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Información Básica</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2" 
                                id="cronograma-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#cronograma-pane" 
                                type="button" 
                                role="tab" 
                                aria-controls="cronograma-pane" 
                                aria-selected="false">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span>Cronograma</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2" 
                                id="documentos-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#documentos-pane" 
                                type="button" 
                                role="tab" 
                                aria-controls="documentos-pane" 
                                aria-selected="false">
                            <i class="fa-solid fa-paperclip"></i>
                            <span>Documentos Adjuntos</span>
                            <span class="badge bg-primary rounded-pill small ms-1">{{ oferta.documentos ? oferta.documentos.length : 0 }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Contenido de las Pestañas -->
            <div class="tab-content card-body p-4" id="ofertaTabsContent">
                
                <!-- PESTAÑA 1: INFORMACIÓN BÁSICA -->
                <div class="tab-pane fade show active" id="info-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.5px;">Objeto</label>
                                <div class="fs-5 fw-semibold text-dark">{{ oferta.objeto }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.5px;">Descripción / Alcance</label>
                                <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line;">
                                    {{ oferta.descripcion }}
                                </div>
                            </div>

                            <div>
                                <label class="text-uppercase small fw-bold text-muted mb-2" style="letter-spacing: 0.5px;">Clasificación de Bienes y Servicios (UNSPSC)</label>
                                <div class="card border bg-light-subtle" v-if="oferta.actividad">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white">Producto: {{ oferta.actividad.codigo_producto }}</span>
                                            <span class="fw-bold text-dark">{{ oferta.actividad.producto }}</span>
                                        </div>
                                        <div class="row g-2 small text-muted">
                                            <div class="col-md-4">
                                                <strong>Segmento:</strong> {{ oferta.actividad.Segmento }} ({{ oferta.actividad.codigo_segmento }})
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Familia:</strong> {{ oferta.actividad.familia }} ({{ oferta.actividad.codigo_familia }})
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Clase:</strong> {{ oferta.actividad.clase }} ({{ oferta.actividad.codigo_clase }})
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Tarjeta de Presupuesto y Estado -->
                            <div class="card border-0 bg-light p-3 rounded-4 mb-3">
                                <label class="text-uppercase small fw-bold text-muted mb-1">Presupuesto Estimado</label>
                                <div class="fs-3 fw-bold text-primary">
                                    {{ formatearMoneda(oferta.presupuesto, oferta.moneda) }}
                                </div>
                                <div class="small text-muted">Moneda oficial: <span class="fw-bold text-dark">{{ oferta.moneda }}</span></div>
                            </div>

                            <div class="card border p-3 rounded-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Estado actual:</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle badge-estado">
                                        {{ oferta.estado }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Consecutivo:</span>
                                    <span class="fw-bold">{{ oferta.consecutivo }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Registrado el:</span>
                                    <span class="small">{{ oferta.creado_en }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 2: CRONOGRAMA -->
                <div class="tab-pane fade" id="cronograma-pane" role="tabpanel" aria-labelledby="cronograma-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-primary-subtle p-4 rounded-4 h-100">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-primary text-white p-3 rounded-circle">
                                        <i class="fa-solid fa-hourglass-start fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="text-uppercase small fw-bold text-primary">Apertura / Inicio</div>
                                        <div class="fs-4 fw-bold text-dark">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                                        <div class="text-muted small">Hora programada: <strong class="text-dark">{{ oferta.hora_inicio }}</strong></div>
                                    </div>
                                </div>
                                <p class="small text-muted mb-0">Momento a partir del cual los oferentes pueden consultar las bases y presentar ofertas.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-danger-subtle p-4 rounded-4 h-100">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-danger text-white p-3 rounded-circle">
                                        <i class="fa-solid fa-hourglass-end fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="text-uppercase small fw-bold text-danger">Cierre de Convocatoria</div>
                                        <div class="fs-4 fw-bold text-dark">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                                        <div class="text-muted small">Hora límite: <strong class="text-dark">{{ oferta.hora_cierre }}</strong></div>
                                    </div>
                                </div>
                                <p class="small text-muted mb-0">Fecha y hora improrrogable para la radicación y cierre de propuestas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 3: DOCUMENTOS ADJUNTOS -->
                <div class="tab-pane fade" id="documentos-pane" role="tabpanel" aria-labelledby="documentos-tab" tabindex="0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Documentos Relacionados a la Oferta</h6>
                        <a :href="'/ofertas/editar/' + oferta.id" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-upload me-1"></i> Gestionar Documentos
                        </a>
                    </div>

                    <div v-if="oferta.documentos && oferta.documentos.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Formato</th>
                                    <th>Fecha de Registro</th>
                                    <th class="text-center">Descarga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="doc in oferta.documentos" :key="doc.id">
                                    <td class="fw-semibold text-dark">{{ doc.titulo }}</td>
                                    <td class="text-muted small">{{ doc.descripcion }}</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" v-if="doc.archivo.endsWith('.pdf')">
                                            <i class="fa-regular fa-file-pdf me-1"></i> PDF
                                        </span>
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle" v-else>
                                            <i class="fa-regular fa-file-zipper me-1"></i> ZIP
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ doc.creado_en }}</td>
                                    <td class="text-center">
                                        <a :href="'/ofertas/' + oferta.id + '/documentos/' + doc.id + '/descargar'" 
                                           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                            <i class="fa-solid fa-download"></i>
                                            <span>Descargar</span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-5 text-muted">
                        <i class="fa-regular fa-folder-open fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">No se encontraron documentos adjuntos para esta oferta.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    window.OFFER_ID = <?= json_encode($ofertaId) ?>;
</script>
<script src="/assets/js/detalle-oferta.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
