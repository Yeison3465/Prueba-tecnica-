<?php require __DIR__ . '/../layouts/header.php'; ?>

<div id="app-listado" v-cloak>
    <!-- Encabezado de página y Acciones -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Listado de Ofertas Registradas</h1>
            <p class="text-muted mb-0">Consulta, filtra y exporta las ofertas del sistema de licitaciones.</p>
        </div>
        <div class="d-flex gap-2">
            <button @click="exportarExcel" class="btn btn-excel d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-excel"></i>
                <span>Descargar Reporte Excel</span>
            </button>
            <a href="/ofertas/crear" class="btn btn-suplos d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Crear Licitación</span>
            </a>
        </div>
    </div>

    <!-- Tarjeta de Filtros -->
    <div class="card card-custom mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">
                    <i class="fa-solid fa-filter me-1"></i> Filtros de Búsqueda
                </span>
                <button v-if="filters.consecutivo || filters.objeto || filters.descripcion" 
                        @click="limpiarFiltros" 
                        class="btn btn-link btn-sm text-danger text-decoration-none p-0">
                    <i class="fa-solid fa-rotate-left me-1"></i> Restablecer filtros
                </button>
            </div>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Consecutivo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fa-solid fa-hashtag"></i>
                        </span>
                        <input type="text" 
                               v-model="filters.consecutivo" 
                               class="form-control border-start-0" 
                               placeholder="Ej. PO-0001-26">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted">Objeto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>
                        <input type="text" 
                               v-model="filters.objeto" 
                               class="form-control border-start-0" 
                               placeholder="Buscar por objeto...">
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label small text-muted">Descripción</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fa-solid fa-align-left"></i>
                        </span>
                        <input type="text" 
                               v-model="filters.descripcion" 
                               class="form-control border-start-0" 
                               placeholder="Buscar por descripción...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ofertas -->
    <div class="card card-custom">
        <div class="card-body p-0">
            <!-- Loading indicator -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando ofertas...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Cargando ofertas...</p>
            </div>

            <!-- Tabla -->
            <div v-else-if="ofertas.length > 0" class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-muted" style="letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">Consecutivo</th>
                            <th>Objeto</th>
                            <th>Descripción</th>
                            <th class="text-center">Fecha Inicio</th>
                            <th class="text-center">Fecha Cierre</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="oferta in ofertas" :key="oferta.id">
                            <td class="ps-4">
                                <span class="badge-consecutivo">{{ oferta.consecutivo }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" :title="oferta.objeto">
                                    {{ oferta.objeto }}
                                </div>
                                <div class="small text-muted" v-if="oferta.actividad">
                                    <i class="fa-solid fa-tag me-1"></i>{{ oferta.actividad.producto }}
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 280px;" :title="oferta.descripcion">
                                    {{ oferta.descripcion }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold">{{ formatearFecha(oferta.fecha_inicio) }}</div>
                                <div class="text-muted small">{{ oferta.hora_inicio }}</div>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold">{{ formatearFecha(oferta.fecha_cierre) }}</div>
                                <div class="text-muted small">{{ oferta.hora_cierre }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle badge-estado" 
                                      v-if="oferta.estado === 'Publicada' || oferta.estado === 'Activo'">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ oferta.estado }}
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle badge-estado" v-else>
                                    {{ oferta.estado }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Botón para ingresar a la vista de detalle (requerido por el PDF) -->
                                    <a :href="'/ofertas/detalle/' + oferta.id" 
                                       class="btn btn-sm btn-outline-primary fw-semibold" 
                                       title="Ver Detalle de la Oferta">
                                        <i class="fa-solid fa-eye me-1"></i> Ver Detalle
                                    </a>
                                    <a :href="'/ofertas/editar/' + oferta.id" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Editar Oferta">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Estado Vacío -->
            <div v-else class="text-center py-5">
                <i class="fa-regular fa-folder-open text-muted fa-3x mb-3"></i>
                <h5 class="fw-bold text-dark">No se encontraron ofertas</h5>
                <p class="text-muted mb-3">No hay registros que coincidan con los criterios de búsqueda.</p>
                <a href="/ofertas/crear" class="btn btn-suplos btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Crear la primera oferta
                </a>
            </div>

            <!-- Barra de Paginación (requerida por el PDF) -->
            <div v-if="pagination.total > 0" class="card-footer bg-white border-top d-flex flex-column flex-md-row justify-content-between align-items-center py-3 px-4 gap-2">
                <div class="text-muted small">
                    Mostrando del <span class="fw-bold">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span>
                    al <span class="fw-bold">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                    de <span class="fw-bold">{{ pagination.total }}</span> resultados
                </div>

                <nav aria-label="Navegación de ofertas">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <button class="page-link" @click="cambiarPagina(pagination.current_page - 1)">
                                <i class="fa-solid fa-chevron-left"></i> Anterior
                            </button>
                        </li>

                        <li class="page-item" 
                            v-for="p in pagination.last_page" 
                            :key="p" 
                            :class="{ active: p === pagination.current_page }">
                            <button class="page-link" @click="cambiarPagina(p)">{{ p }}</button>
                        </li>

                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <button class="page-link" @click="cambiarPagina(pagination.current_page + 1)">
                                Siguiente <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/listado-ofertas.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
