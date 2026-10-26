<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
      <router-link class="navbar-brand fw-bold d-flex align-items-center gap-2" to="/ofertas">
        <i class="fa-solid fa-briefcase"></i>
        <span>Suplos <span class="badge bg-light text-primary fs-6 ms-1">Licitaciones</span></span>
      </router-link>
      
      <button class="navbar-toggler" 
              type="button" 
              data-bs-toggle="collapse" 
              data-bs-target="#navbarVue" 
              aria-controls="navbarVue" 
              aria-expanded="false" 
              aria-label="Navegación">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarVue">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link class="nav-link" to="/ofertas" active-class="active">
              <i class="fa-solid fa-list-check me-1"></i> Ofertas
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/ofertas/crear" active-class="active">
              <i class="fa-solid fa-plus me-1"></i> Nueva Oferta
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
