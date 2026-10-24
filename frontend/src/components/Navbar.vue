<template>
  <header class="app-header">
    <!-- Barra superior de acento con colores de la empresa Suplos -->
    <div class="suplos-accent-bar"></div>

    <nav class="navbar navbar-expand-lg navbar-suplos-clean bg-white border-bottom">
      <div class="container">
        <!-- Logo Corporativo Suplos -->
        <router-link class="navbar-brand d-flex align-items-center gap-2 text-decoration-none py-1" to="/ofertas">
          <span class="brand-text">suplos<span class="brand-dot">.</span></span>
          <span class="brand-badge">Licitaciones</span>
        </router-link>
        
        <button class="navbar-toggler border-0 shadow-none text-dark" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarVue" 
                aria-controls="navbarVue" 
                aria-expanded="false" 
                aria-label="Abrir navegación">
          <i class="fa-solid fa-bars-staggered"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarVue">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
            <li class="nav-item">
              <router-link class="nav-link nav-link-minimal px-3 py-2" to="/ofertas" active-class="active">
                <i class="fa-solid fa-list-check me-1 small"></i>
                <span>Listado de Licitaciones</span>
              </router-link>
            </li>
            
            <li class="nav-item ms-lg-2">
              <router-link class="btn btn-suplos-cyan btn-sm px-3 py-2 shadow-none d-flex align-items-center gap-2" to="/ofertas/crear">
                <i class="fa-solid fa-plus small"></i>
                <span>Nueva Oferta</span>
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 1020;
  background-color: #ffffff;
}

/* Barra superior minimalista con degradado corporativo Suplos */
.suplos-accent-bar {
  height: 3px;
  background: linear-gradient(90deg, #0b2853 0%, #009fe3 100%);
  width: 100%;
}

.navbar-suplos-clean {
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  border-color: #e2e8f0 !important;
  box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.02);
}

.brand-text {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.6px;
  color: #0b2853;
  line-height: 1;
}

.brand-dot {
  color: #009fe3;
  font-size: 1.75rem;
  line-height: 0;
}

.brand-badge {
  background-color: #f0f9ff;
  color: #0284c7;
  border: 1px solid #bae6fd;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.2rem 0.5rem;
  margin-left: 0.2rem;
}

.nav-link-minimal {
  color: #475569 !important;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.15s ease;
}

.nav-link-minimal:hover {
  color: #0b2853 !important;
  background-color: #f8fafc;
}

.nav-link-minimal.active {
  color: #0b2853 !important;
  background-color: #f1f5f9;
}
</style>
