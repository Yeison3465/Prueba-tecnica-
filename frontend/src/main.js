// 1. Estilos globales de Bootstrap 5.3 primero (para que el diseño personalizado tenga precedencia en la cascada)
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// 2. Vue y componentes de la aplicación
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

const app = createApp(App);

app.use(router);
app.mount('#app');
