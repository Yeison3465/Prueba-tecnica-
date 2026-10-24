<template>
  <nav class="navbar navbar-expand-lg navbar-dark navbar-suplos shadow-sm">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center gap-2" to="/ofertas">
        <i class="fa-solid fa-layer-group text-info"></i>
        <span>SUPLOS</span>
        <span class="brand-badge ms-1">Ofertas Estándar</span>
      </router-link>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVue" aria-controls="navbarVue" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarVue">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
          <li class="nav-item">
            <router-link class="nav-link text-white fw-semibold px-3" to="/ofertas" active-class="active">
              <i class="fa-solid fa-list-check me-1"></i> Listado de Ofertas
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="btn btn-light btn-sm text-primary fw-bold px-3 shadow-sm" to="/ofertas/crear">
              <i class="fa-solid fa-plus-circle me-1"></i> Nueva Oferta
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar-suplos {
  background: linear-gradient(135deg, #072a5e 0%, #0b3b80 60%, #00a4e4 100%);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.brand-badge {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 6px;
  font-size: 0.72rem;
  padding: 3px 8px;
  font-weight: 600;
  text-transform: uppercase;
}
</style>
