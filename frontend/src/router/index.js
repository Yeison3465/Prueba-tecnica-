import { createRouter, createWebHistory } from 'vue-router';
import ListadoOfertas from '../views/ListadoOfertas.vue';
import CrearOferta from '../views/CrearOferta.vue';
import EditarOferta from '../views/EditarOferta.vue';
import DetalleOferta from '../views/DetalleOferta.vue';

const routes = [
  {
    path: '/',
    redirect: '/ofertas',
  },
  {
    path: '/ofertas',
    name: 'ListadoOfertas',
    component: ListadoOfertas,
  },
  {
    path: '/ofertas/crear',
    name: 'CrearOferta',
    component: CrearOferta,
  },
  {
    path: '/ofertas/editar/:id',
    name: 'EditarOferta',
    component: EditarOferta,
    props: true,
  },
  {
    path: '/ofertas/detalle/:id',
    name: 'DetalleOferta',
    component: DetalleOferta,
    props: true,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
