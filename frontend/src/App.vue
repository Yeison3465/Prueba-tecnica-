<template>
  <div class="d-flex flex-column min-vh-100 bg-light">
    <Navbar />
    
    <main class="container py-2 flex-grow-1">
      <router-view />
    </main>

    <footer class="py-3 bg-white border-top mt-auto">
      <div class="container text-center text-muted small">
        <span>Gestión de Licitaciones y Ofertas &copy; {{ new Date().getFullYear() }}</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
</script>
