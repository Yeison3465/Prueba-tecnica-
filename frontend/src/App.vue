<template>
  <div class="app-layout">
    <Navbar />
    
    <main class="container py-4">
      <router-view />
    </main>

    <footer class="text-center mt-auto py-3 bg-white border-top">
      <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-muted small">
        <div>
          <span class="fw-semibold text-dark">Suplos</span> &copy; {{ new Date().getFullYear() }} - Módulo de Licitaciones y Ofertas Estándar.
        </div>
        <div>
          Frontend Reactivo: <span class="badge bg-primary text-white">Vue.js 3 (SFC) + Vite + Bootstrap 5</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
</script>

<style>
:root {
  --primary-color: #0b3b80;
  --primary-hover: #072a5e;
  --secondary-color: #00a4e4;
  --light-bg: #f8fafc;
  --card-border: #e2e8f0;
  --text-main: #1e293b;
}

body {
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background-color: var(--light-bg);
  color: var(--text-main);
  min-height: 100vh;
}

.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.card-custom {
  background: #ffffff;
  border: 1px solid var(--card-border);
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.card-custom-header {
  background: #ffffff;
  border-bottom: 1px solid var(--card-border);
  padding: 1.25rem 1.5rem;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.badge-consecutivo {
  background-color: #e0f2fe;
  color: #0369a1;
  font-weight: 700;
  font-family: 'Consolas', monospace;
  padding: 0.35rem 0.65rem;
  border-radius: 6px;
  border: 1px solid #bae6fd;
}

.btn-suplos {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  color: #ffffff;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-suplos:hover:not(:disabled) {
  background-color: var(--primary-hover);
  border-color: var(--primary-hover);
  color: #ffffff;
  transform: translateY(-1px);
}

.btn-excel {
  background-color: #107c41;
  border-color: #107c41;
  color: #ffffff;
  font-weight: 600;
  border-radius: 8px;
}

.btn-excel:hover {
  background-color: #0b592f;
  color: #ffffff;
}

.char-counter {
  font-size: 0.8rem;
  font-weight: 500;
  color: #64748b;
}

.char-counter.limit-near {
  color: #d97706;
}

.char-counter.limit-reached {
  color: #dc2626;
  font-weight: 700;
}
</style>
