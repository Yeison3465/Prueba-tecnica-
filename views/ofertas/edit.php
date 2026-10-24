<?php require __DIR__ . '/../layouts/header.php'; ?>

<div id="app-form-oferta" v-cloak>
    <!-- Encabezado de la página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="/ofertas" class="text-decoration-none text-muted">Ofertas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Oferta</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <h1 class="h3 fw-bold text-dark mb-0">Formulario de Edición de Oferta</h1>
                <span class="badge-consecutivo" v-if="form.consecutivo">{{ form.consecutivo }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a :href="'/ofertas/detalle/' + ofertaId" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-eye"></i>
                <span>Ver Detalle</span>
            </a>
            <a href="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver al Listado</span>
            </a>
        </div>
    </div>

    <form @submit.prevent="guardarOferta" novalidate>
        <!-- SECCIÓN 1: INFORMACIÓN BÁSICA (PRECARGADA) -->
        <div class="card card-custom mb-4">
            <div class="card-custom-header d-flex justify-content-between align-items-center">
                <div class="section-header-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span>SECCIÓN 1 – Información Básica</span>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                    Consecutivo: {{ form.consecutivo || 'Cargando...' }}
                </span>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Objeto (Máx 150 caracteres con contador) -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">
                                Objeto de la Oferta <span class="text-danger">*</span>
                            </label>
                            <span class="char-counter" :class="{ 'limit-near': objetoLength > 130, 'limit-reached': objetoLength >= 150 }">
                                {{ objetoLength }} / 150 caracteres
                            </span>
                        </div>
                        <input type="text" 
                               v-model="form.objeto" 
                               maxlength="150" 
                               class="form-control" 
                               :class="{ 'is-invalid': form.objeto.length > 0 && !isObjetoValido }"
                               required>
                        <div class="invalid-feedback">
                            El objeto es obligatorio, no debe ser solo espacios y no puede exceder 150 caracteres.
                        </div>
                    </div>

                    <!-- Descripción / Alcance (Máx 400 caracteres con contador) -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">
                                Descripción / Alcance <span class="text-danger">*</span>
                            </label>
                            <span class="char-counter" :class="{ 'limit-near': descripcionLength > 360, 'limit-reached': descripcionLength >= 400 }">
                                {{ descripcionLength }} / 400 caracteres
                            </span>
                        </div>
                        <textarea v-model="form.descripcion" 
                                  maxlength="400" 
                                  rows="3" 
                                  class="form-control" 
                                  :class="{ 'is-invalid': form.descripcion.length > 0 && !isDescripcionValida }"
                                  required></textarea>
                        <div class="invalid-feedback">
                            La descripción es obligatoria, no debe ser solo espacios y no puede exceder 400 caracteres.
                        </div>
                    </div>

                    <!-- Moneda -->
                    <div class="col-md-3">
                        <label class="form-label">Moneda <span class="text-danger">*</span></label>
                        <select v-model="form.moneda" class="form-select" required>
                            <option value="COP">COP (Peso Colombiano)</option>
                            <option value="USD">USD (Dólar Estadounidense)</option>
                            <option value="EUR">EUR (Euro)</option>
                        </select>
                    </div>

                    <!-- Presupuesto -->
                    <div class="col-md-3">
                        <label class="form-label">Presupuesto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">{{ form.moneda }}</span>
                            <input type="text" 
                                   v-model="form.presupuesto" 
                                   class="form-control" 
                                   :class="{ 'is-invalid': form.presupuesto !== '' && !isPresupuestoValido }"
                                   required>
                        </div>
                        <div class="invalid-feedback d-block" v-if="form.presupuesto !== '' && !isPresupuestoValido">
                            Debe ser un número mayor a 0 con máximo dos decimales.
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select v-model="form.estado" class="form-select">
                            <option value="Publicada">Publicada</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Cerrada">Cerrada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>

                    <!-- Actividad UNSPSC -->
                    <div class="col-md-4">
                        <label class="form-label">Actividad UNSPSC <span class="text-danger">*</span></label>
                        <select v-model="form.actividad_id" class="form-select" :disabled="loadingActividades" required>
                            <option value="" disabled>-- Seleccione una actividad --</option>
                            <option v-for="act in actividades" :key="act.id" :value="act.id">
                                {{ act.producto }} (Cód. {{ act.codigo_producto }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: CRONOGRAMA (PRECARGADO) -->
        <div class="card card-custom mb-4">
            <div class="card-custom-header d-flex justify-content-between align-items-center">
                <div class="section-header-title">
                    <i class="fa-solid fa-calendar-days text-primary"></i>
                    <span>SECCIÓN 2 – Cronograma</span>
                </div>
            </div>

            <div class="card-body p-4">
                <div v-if="errorCronograma" class="alert alert-warning d-flex align-items-center gap-2 mb-4 py-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning fa-lg"></i>
                    <span class="small fw-semibold">{{ errorCronograma }}</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" v-model="form.fecha_inicio" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hora de Inicio (24h) <span class="text-danger">*</span></label>
                        <input type="time" v-model="form.hora_inicio" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha de Cierre <span class="text-danger">*</span></label>
                        <input type="date" v-model="form.fecha_cierre" :min="form.fecha_inicio" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hora de Cierre (24h) <span class="text-danger">*</span></label>
                        <input type="time" v-model="form.hora_cierre" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: DOCUMENTOS (REQUERIDO POR EL PDF) -->
        <div class="card card-custom mb-4">
            <div class="card-custom-header d-flex justify-content-between align-items-center">
                <div class="section-header-title">
                    <i class="fa-solid fa-paperclip text-primary"></i>
                    <span>SECCIÓN 3 – Documentos de la Oferta</span>
                </div>
                <!-- Botón para Agregar Documento (despliega Modal) -->
                <button type="button" 
                        class="btn btn-sm btn-suplos d-flex align-items-center gap-2" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalAgregarDocumento">
                    <i class="fa-solid fa-plus"></i>
                    <span>Agregar Documento</span>
                </button>
            </div>

            <div class="card-body p-4">
                <!-- Advertencia de regla obligatoria del PDF -->
                <div v-if="documentos.length === 0" class="alert alert-danger d-flex align-items-center gap-3 mb-3">
                    <i class="fa-solid fa-triangle-exclamation fa-2x text-danger"></i>
                    <div>
                        <div class="fw-bold">Regla obligatoria de edición:</div>
                        <div class="small">En edición debe existir al menos 1 documento cargado (PDF o ZIP). Debe agregar un documento para poder habilitar el guardado final.</div>
                    </div>
                </div>

                <!-- Tabla de Documentos Registrados -->
                <div v-if="documentos.length > 0" class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Archivo</th>
                                <th>Fecha Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="doc in documentos" :key="doc.id">
                                <td class="fw-semibold text-dark">{{ doc.titulo }}</td>
                                <td class="text-muted small">{{ doc.descripcion }}</td>
                                <td>
                                    <a :href="'/ofertas/' + ofertaId + '/documentos/' + doc.id + '/descargar'" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-download"></i>
                                        <span>Descargar archivo</span>
                                    </a>
                                </td>
                                <td class="text-muted small">{{ doc.creado_en }}</td>
                                <td class="text-center">
                                    <button type="button" 
                                            @click="eliminarDocumento(doc.id)" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="Eliminar documento">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Estado sin documentos -->
                <div v-else class="text-center py-4 text-muted">
                    <i class="fa-regular fa-file-pdf fa-2x mb-2 text-danger opacity-50"></i>
                    <p class="mb-2">Aún no se ha cargado ningún documento.</p>
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAgregarDocumento">
                        <i class="fa-solid fa-upload me-1"></i> Cargar documento obligatorio
                    </button>
                </div>
            </div>
        </div>

        <!-- Barra inferior de acciones -->
        <div class="card card-custom">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
                <div class="text-muted small">
                    <span v-if="!isDocumentosValido" class="text-danger fw-bold">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Falta adjuntar al menos un documento.
                    </span>
                    <span v-else-if="!isFormValido" class="text-warning fw-semibold">
                        <i class="fa-solid fa-circle-info me-1"></i> Por favor verifique las validaciones de los campos requeridos.
                    </span>
                    <span v-else class="text-success fw-semibold">
                        <i class="fa-solid fa-circle-check me-1"></i> Todos los campos y requisitos validados correctamente.
                    </span>
                </div>

                <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                    <a href="/ofertas" class="btn btn-outline-secondary px-4">Cancelar</a>
                    
                    <!-- Botón Guardar Cambios: Solo se habilita si todas las validaciones + mínimo 1 doc se cumplen -->
                    <button type="submit" 
                            class="btn btn-suplos px-4 d-flex align-items-center gap-2 shadow-sm"
                            :disabled="!isFormValido">
                        <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-floppy-disk"></i>
                        <span>{{ guardando ? 'Guardando cambios...' : 'Guardar Cambios' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- MODAL PARA AGREGAR DOCUMENTO (REQUERIDO POR EL PDF) -->
    <div class="modal fade" id="modalAgregarDocumento" tabindex="-1" aria-labelledby="modalDocLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalDocLabel">
                        <i class="fa-solid fa-file-circle-plus text-primary me-2"></i>
                        Agregar Documento a la Oferta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form @submit.prevent="agregarDocumento">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Título del Documento <span class="text-danger">*</span></label>
                            <input type="text" 
                                   v-model="nuevoDoc.titulo" 
                                   maxlength="100" 
                                   class="form-control" 
                                   placeholder="Ej. Términos de Referencia" 
                                   required>
                            <div class="form-text small">Texto registrado (Máx 100 caracteres).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción Corta <span class="text-danger">*</span></label>
                            <textarea v-model="nuevoDoc.descripcion" 
                                      maxlength="200" 
                                      rows="2" 
                                      class="form-control" 
                                      placeholder="Breve detalle del archivo adjunto..." 
                                      required></textarea>
                            <div class="form-text small">Descripción corta (Máx 200 caracteres).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Archivo Adjunto <span class="text-danger">*</span></label>
                            <input type="file" 
                                   id="modalFileInput" 
                                   @change="onFileChange" 
                                   class="form-control" 
                                   accept=".pdf,.zip" 
                                   required>
                            <div class="form-text small text-primary fw-semibold">
                                <i class="fa-solid fa-circle-info me-1"></i> Formatos admitidos estrictos: Únicamente <b>PDF</b> o <b>ZIP</b>.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" 
                                class="btn btn-suplos btn-sm d-flex align-items-center gap-2" 
                                :disabled="!isNuevoDocValido">
                            <i v-if="subiendoDoc" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-plus"></i>
                            <span>{{ subiendoDoc ? 'Subiendo...' : 'Agregar al Listado' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.OFFER_ID = <?= json_encode($ofertaId) ?>;
</script>
<script src="/assets/js/form-oferta.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
