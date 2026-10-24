<?php

/**
 * Front Controller: Punto de entrada único para la aplicación PHP MVC.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Router;

// Inicializar conexión a BD y Eloquent Capsule
try {
    Database::init();
} catch (\Throwable $e) {
    // Si la BD aún no está iniciada en MySQL, registramos el error
    error_log("Error de conexión a BD: " . $e->getMessage());
}

$router = new Router();

// ================= Rutas Web (SPA Vue.js 3 o Vistas PHP) =================
$spaIndex = __DIR__ . '/dist/index.html';
$serveSpa = function () use ($spaIndex) {
    if (file_exists($spaIndex)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($spaIndex);
        exit;
    }
};

if (file_exists($spaIndex)) {
    $router->get('/', $serveSpa);
    $router->get('/ofertas', $serveSpa);
    $router->get('/ofertas/crear', $serveSpa);
    $router->get('/ofertas/editar/{id}', $serveSpa);
    $router->get('/ofertas/detalle/{id}', $serveSpa);
} else {
    $router->get('/', 'OfertaController@index');
    $router->get('/ofertas', 'OfertaController@index');
    $router->get('/ofertas/crear', 'OfertaController@create');
    $router->get('/ofertas/editar/{id}', 'OfertaController@edit');
    $router->get('/ofertas/detalle/{id}', 'OfertaController@show');
}

$router->get('/ofertas/exportar-excel', 'ReporteController@exportarExcel');
$router->get('/ofertas/{id}/documentos/{docId}/descargar', 'OfertaController@downloadDocumento');

// ================= Rutas de API JSON =================
// Actividades UNSPSC
$router->get('/api/actividades', 'ActividadController@index');
$router->get('/api/actividades/{id}', 'ActividadController@show');

// Ofertas
$router->get('/api/ofertas', 'OfertaController@apiList');
$router->post('/api/ofertas', 'OfertaController@store');
$router->get('/api/ofertas/{id}', 'OfertaController@apiGet');
$router->post('/api/ofertas/{id}', 'OfertaController@update');
$router->put('/api/ofertas/{id}', 'OfertaController@update');

// Documentos adjuntos
$router->post('/api/ofertas/{id}/documentos', 'OfertaController@uploadDocumento');
$router->delete('/api/ofertas/{id}/documentos/{docId}', 'OfertaController@deleteDocumento');

// Despachar la petición
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
