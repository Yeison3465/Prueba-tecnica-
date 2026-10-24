<template>
  <div class="app-layout">
    <Navbar />
    
    <main class="container py-4 flex-grow-1">
      <router-view />
    </main>

    <footer class="app-footer py-3 bg-white border-top">
      <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-muted small">
        <div class="d-flex align-items-center gap-2">
          <span class="brand-logo-text text-dark">suplos<span class="text-cyan">.</span></span>
          <span class="text-secondary opacity-50">|</span>
          <span class="fw-normal">Gestión de Licitaciones y Ofertas &copy; {{ new Date().getFullYear() }}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <span class="status-indicator"></span>
            <span class="text-secondary small">Sistema Operativo</span>
          </div>
          <span class="badge badge-tech">Vue.js 3 + Bootstrap 5</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
</script>

<style>
/* ==========================================================================
   SISTEMA DE DISEÑO MINIMALISTA SUPLOS
   Colores Corporativos Oficiales:
   - Navy Principal: #0b2853
   - Cyan Eléctrico / Accent: #009fe3
   - Slate Neutros: #0f172a, #334155, #64748b, #94a3b8, #cbd5e1, #e2e8f0, #f8fafc
   ========================================================================== */
:root {
  --suplos-navy: #0b2853;
  --suplos-navy-hover: #071b38;
  --suplos-navy-light: #12356c;
  --suplos-cyan: #009fe3;
  --suplos-cyan-hover: #0088c4;
  --suplos-cyan-light: #f0f9ff;
  --suplos-cyan-border: #bae6fd;
  --suplos-bg: #f8fafc;
  --suplos-card: #ffffff;
  --suplos-border: #e2e8f0;
  --suplos-border-subtle: #f1f5f9;
  --suplos-text: #0f172a;
  --suplos-body: #334155;
  --suplos-muted: #64748b;
  --suplos-radius: 12px;
  --suplos-radius-sm: 8px;
}

body {
  font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: var(--suplos-bg);
  color: var(--suplos-body);
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.5;
}

.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.text-navy {
  color: var(--suplos-navy) !important;
}

.text-cyan {
  color: var(--suplos-cyan) !important;
}

.brand-logo-text {
  font-weight: 800;
  font-size: 1.15rem;
  letter-spacing: -0.5px;
}

/* Tarjetas y Contenedores Minimalistas */
.card-custom {
  background: var(--suplos-card);
  border: 1px solid var(--suplos-border);
  border-radius: var(--suplos-radius);
  box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.02);
  transition: all 0.2s ease;
}

.card-custom-header {
  background: #ffffff;
  border-bottom: 1px solid var(--suplos-border);
  padding: 1.1rem 1.5rem;
  border-top-left-radius: var(--suplos-radius);
  border-top-right-radius: var(--suplos-radius);
}

.section-title {
  font-size: 0.925rem;
  font-weight: 700;
  color: var(--suplos-navy);
  letter-spacing: -0.2px;
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.section-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background-color: var(--suplos-cyan-light);
  color: var(--suplos-cyan);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

/* Formularios Minimalistas */
.form-label {
  font-weight: 600;
  color: #334155;
  font-size: 0.815rem;
  margin-bottom: 0.4rem;
  letter-spacing: -0.1px;
}

.form-control, .form-select {
  border: 1px solid #cbd5e1;
  border-radius: var(--suplos-radius-sm);
  padding: 0.625rem 0.85rem;
  font-size: 0.9rem;
  color: var(--suplos-text);
  background-color: #ffffff;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--suplos-cyan);
  box-shadow: 0 0 0 3px rgba(0, 159, 227, 0.16);
  outline: none;
}

.form-control::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

/* Badge Consecutivo Corporativo */
.badge-consecutivo {
  background-color: var(--suplos-cyan-light);
  color: #0369a1;
  font-weight: 700;
  font-family: 'JetBrains Mono', 'SFMono-Regular', Consolas, Menlo, monospace;
  font-size: 0.8rem;
  padding: 0.35rem 0.65rem;
  border-radius: 6px;
  border: 1px solid var(--suplos-cyan-border);
  letter-spacing: 0.3px;
  display: inline-flex;
  align-items: center;
}

/* Botones con los Colores de la Empresa */
.btn-suplos {
  --bs-btn-color: #ffffff;
  --bs-btn-bg: var(--suplos-navy);
  --bs-btn-border-color: var(--suplos-navy);
  --bs-btn-hover-color: #ffffff;
  --bs-btn-hover-bg: var(--suplos-navy-hover);
  --bs-btn-hover-border-color: var(--suplos-navy-hover);
  --bs-btn-active-color: #ffffff;
  --bs-btn-active-bg: var(--suplos-navy-hover);
  --bs-btn-active-border-color: var(--suplos-navy-hover);
  --bs-btn-disabled-color: #ffffff;
  --bs-btn-disabled-bg: #cbd5e1;
  --bs-btn-disabled-border-color: #cbd5e1;
  background-color: var(--suplos-navy) !important;
  border: 1px solid var(--suplos-navy) !important;
  color: #ffffff !important;
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: var(--suplos-radius-sm);
  padding: 0.55rem 1.25rem;
  transition: all 0.15s ease;
}

.btn-suplos:hover:not(:disabled) {
  background-color: var(--suplos-navy-hover) !important;
  border-color: var(--suplos-navy-hover) !important;
  color: #ffffff !important;
  box-shadow: 0 2px 6px rgba(11, 40, 83, 0.2);
}

.btn-suplos:disabled {
  background-color: #cbd5e1 !important;
  border-color: #cbd5e1 !important;
  color: #ffffff !important;
  cursor: not-allowed;
  opacity: 0.8;
}

.btn-suplos-cyan {
  --bs-btn-color: #ffffff;
  --bs-btn-bg: var(--suplos-cyan);
  --bs-btn-border-color: var(--suplos-cyan);
  --bs-btn-hover-color: #ffffff;
  --bs-btn-hover-bg: var(--suplos-cyan-hover);
  --bs-btn-hover-border-color: var(--suplos-cyan-hover);
  --bs-btn-active-color: #ffffff;
  --bs-btn-active-bg: var(--suplos-cyan-hover);
  --bs-btn-active-border-color: var(--suplos-cyan-hover);
  background-color: var(--suplos-cyan) !important;
  border: 1px solid var(--suplos-cyan) !important;
  color: #ffffff !important;
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: var(--suplos-radius-sm);
  padding: 0.55rem 1.25rem;
  transition: all 0.15s ease;
}

.btn-suplos-cyan:hover:not(:disabled) {
  background-color: var(--suplos-cyan-hover) !important;
  border-color: var(--suplos-cyan-hover) !important;
  color: #ffffff !important;
  box-shadow: 0 2px 6px rgba(0, 159, 227, 0.25);
}

.btn-outline-minimal {
  --bs-btn-color: #475569;
  --bs-btn-bg: #ffffff;
  --bs-btn-border-color: #cbd5e1;
  --bs-btn-hover-color: var(--suplos-navy);
  --bs-btn-hover-bg: #f8fafc;
  --bs-btn-hover-border-color: #94a3b8;
  background-color: #ffffff !important;
  border: 1px solid #cbd5e1 !important;
  color: #475569 !important;
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: var(--suplos-radius-sm);
  padding: 0.55rem 1.15rem;
  transition: all 0.15s ease;
}

.btn-outline-minimal:hover {
  background-color: #f8fafc !important;
  border-color: #94a3b8 !important;
  color: var(--suplos-navy) !important;
}

.btn-excel {
  --bs-btn-color: #ffffff;
  --bs-btn-bg: #059669;
  --bs-btn-border-color: #059669;
  --bs-btn-hover-color: #ffffff;
  --bs-btn-hover-bg: #047857;
  --bs-btn-hover-border-color: #047857;
  background-color: #059669 !important;
  border: 1px solid #059669 !important;
  color: #ffffff !important;
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: var(--suplos-radius-sm);
  padding: 0.55rem 1.15rem;
  transition: all 0.15s ease;
}

.btn-excel:hover {
  background-color: #047857 !important;
  border-color: #047857 !important;
  color: #ffffff !important;
  box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
}

/* Contador de Caracteres Minimalista */
.char-counter-tag {
  background-color: #f1f5f9;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.55rem;
  border-radius: 6px;
  letter-spacing: 0.2px;
  font-variant-numeric: tabular-nums;
  border: 1px solid #e2e8f0;
}

.char-counter-tag.limit-near {
  background-color: #fffbeb;
  color: #b45309;
  border-color: #fde68a;
}

.char-counter-tag.limit-reached {
  background-color: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
}

/* Indicador de estado y badges auxiliares */
.status-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #10b981;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.badge-tech {
  background-color: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.25rem 0.55rem;
}

.app-footer {
  border-color: var(--suplos-border) !important;
}

/* Personalización de SweetAlert2 con colores corporativos */
.swal2-popup {
  font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
  border-radius: 14px !important;
  border: 1px solid var(--suplos-border) !important;
}

.swal2-confirm.btn-suplos, 
.swal2-styled.swal2-confirm {
  background-color: var(--suplos-navy) !important;
  border-radius: 8px !important;
  font-weight: 600 !important;
  box-shadow: none !important;
}

.swal2-cancel.swal2-styled {
  border-radius: 8px !important;
  font-weight: 600 !important;
  box-shadow: none !important;
}
</style>
