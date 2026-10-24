<?php require __DIR__ . '/../layouts/header.php'; ?>

<div id="app-form-oferta" v-cloak>
    <!-- Encabezado de la página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="/ofertas" class="text-decoration-none text-muted">Ofertas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Crear Licitación</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0">Formulario de Creación de Oferta</h1>
        </div>
        <div>
            <a href="/ofertas" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver al Listado</span>
            </a>
        </div>
    </div>

    <form @submit.prevent="guardarOferta" novalidate>
        <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
        <div class="card card-custom mb-4">
            <div class="card-custom-header d-flex justify-content-between align-items-center">
                <div class="section-header-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span>SECCIÓN 1 – Información Básica</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Paso 1 de 2</span>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Objeto (Máx 150 caracteres con contador en vivo) -->
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
                               placeholder="Ingrese el objeto de la licitación..."
                               required>
                        <div class="invalid-feedback">
                            El objeto es obligatorio, no debe ser solo espacios y no puede exceder 150 caracteres.
                        </div>
                        <div class="form-text text-muted small">
                            Breve resumen o título identificador del requerimiento.
                        </div>
                    </div>

                    <!-- Descripción / Alcance (Máx 400 caracteres con contador en vivo) -->
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
                                  placeholder="Detalle el alcance de la oferta o términos generales..."
                                  required></textarea>
                        <div class="invalid-feedback">
                            La descripción es obligatoria, no debe ser solo espacios y no puede exceder 400 caracteres.
                        </div>
                    </div>

                    <!-- Moneda (Select fijo: COP, USD, EUR) -->
                    <div class="col-md-3">
                        <label class="form-label">
                            Moneda <span class="text-danger">*</span>
                        </label>
                        <select v-model="form.moneda" class="form-select" required>
                            <option value="COP">COP (Peso Colombiano)</option>
                            <option value="USD">USD (Dólar Estadounidense)</option>
                            <option value="EUR">EUR (Euro)</option>
                        </select>
                        <div class="form-text text-muted small">Valores permitidos fijos.</div>
                    </div>

                    <!-- Presupuesto (Decimal > 0 con máx 2 decimales) -->
                    <div class="col-md-4">
                        <label class="form-label">
                            Presupuesto <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">{{ form.moneda }}</span>
                            <input type="text" 
                                   v-model="form.presupuesto" 
                                   class="form-control" 
                                   :class="{ 'is-invalid': form.presupuesto !== '' && !isPresupuestoValido }"
                                   placeholder="Ej. 1500000.50"
                                   required>
                        </div>
                        <div class="invalid-feedback d-block" v-if="form.presupuesto !== '' && !isPresupuestoValido">
                            Debe ser un número mayor a 0 con máximo dos decimales (ej. 1000.50).
                        </div>
                        <div class="form-text text-muted small" v-else>
                            Solo números y punto decimal (máximo 2 decimales).
                        </div>
                    </div>

                    <!-- Actividad UNSPSC (Select dinámico cargado vía Axios) -->
                    <div class="col-md-5">
                        <label class="form-label">
                            Actividad (Clasificador UNSPSC) <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <select v-model="form.actividad_id" 
                                    class="form-select" 
                                    :disabled="loadingActividades"
                                    required>
                                <option value="" disabled>
                                    {{ loadingActividades ? 'Cargando actividades desde base de datos...' : '-- Seleccione una actividad --' }}
                                </option>
                                <option v-for="act in actividades" :key="act.id" :value="act.id">
                                    {{ act.producto }} (Cód. {{ act.codigo_producto }}) - {{ act.Segmento }}
                                </option>
                            </select>
                        </div>
                        <div class="form-text text-muted small">
                            <span v-if="loadingActividades" class="text-primary">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando maestra de bienes y servicios...
                            </span>
                            <span v-else>
                                Cargadas {{ actividades.length }} actividades desde la BD.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: CRONOGRAMA -->
        <div class="card card-custom mb-4">
            <div class="card-custom-header d-flex justify-content-between align-items-center">
                <div class="section-header-title">
                    <i class="fa-solid fa-calendar-days text-primary"></i>
                    <span>SECCIÓN 2 – Cronograma</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Paso 2 de 2</span>
            </div>

            <div class="card-body p-4">
                <!-- Alerta de validación cronológica cruzada -->
                <div v-if="errorCronograma" class="alert alert-warning d-flex align-items-center gap-2 mb-4 py-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning fa-lg"></i>
                    <span class="small fw-semibold">{{ errorCronograma }}</span>
                </div>

                <div class="row g-4">
                    <!-- Fecha y Hora de Inicio -->
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" 
                               v-model="form.fecha_inicio" 
                               class="form-control" 
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hora de Inicio (24h) <span class="text-danger">*</span></label>
                        <input type="time" 
                               v-model="form.hora_inicio" 
                               class="form-control" 
                               required>
                    </div>

                    <!-- Fecha y Hora de Cierre -->
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Cierre <span class="text-danger">*</span></label>
                        <input type="date" 
                               v-model="form.fecha_cierre" 
                               :min="form.fecha_inicio"
                               class="form-control" 
                               :class="{ 'is-invalid': form.fecha_cierre && form.fecha_cierre < form.fecha_inicio }"
                               required>
                        <div class="invalid-feedback">No puede ser menor a fecha inicio.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hora de Cierre (24h) <span class="text-danger">*</span></label>
                        <input type="time" 
                               v-model="form.hora_cierre" 
                               class="form-control" 
                               required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra inferior de acciones -->
        <div class="card card-custom">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3">
                <div class="text-muted small">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i>
                    El consecutivo único <code>PO-000X-YY</code> será generado automáticamente por el backend al guardar.
                </div>

                <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                    <a href="/ofertas" class="btn btn-outline-secondary px-4">Cancelar</a>
                    
                    <!-- Botón Guardar: SOLO se habilita si se cumplen todas las validaciones -->
                    <button type="submit" 
                            class="btn btn-suplos px-4 d-flex align-items-center gap-2 shadow-sm"
                            :disabled="!isFormValido">
                        <i v-if="guardando" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-check"></i>
                        <span>{{ guardando ? 'Guardando...' : 'Guardar Oferta' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    window.OFFER_ID = null;
</script>
<script src="/assets/js/form-oferta.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
