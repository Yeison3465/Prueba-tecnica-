<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        // Convertir {param} en expresión regular nombrada
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . rtrim($pattern, '/') . "$#";

        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        // Soporte para spoofing de método (_method en POST)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Caso ruta raíz
            if ($route['path'] === '/' && $uri === '/') {
                $this->executeHandler($route['handler'], []);
                return;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // Si se solicita un archivo estático existente en public/ (ej. JS, CSS, imágenes, uploads), servirlo con su MIME type
        $publicFile = __DIR__ . '/../../public' . $uri;
        if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
            $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
            $mimes = [
                'js'    => 'application/javascript; charset=utf-8',
                'mjs'   => 'application/javascript; charset=utf-8',
                'css'   => 'text/css; charset=utf-8',
                'png'   => 'image/png',
                'jpg'   => 'image/jpeg',
                'jpeg'  => 'image/jpeg',
                'gif'   => 'image/gif',
                'svg'   => 'image/svg+xml',
                'ico'   => 'image/x-icon',
                'pdf'   => 'application/pdf',
                'zip'   => 'application/zip',
                'json'  => 'application/json',
                'woff'  => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf'   => 'font/ttf',
            ];
            $contentType = $mimes[$ext] ?? 'application/octet-stream';
            header("Content-Type: {$contentType}");
            header('Content-Length: ' . filesize($publicFile));
            readfile($publicFile);
            exit;
        }

        // Si es una petición GET de página web y existe la SPA compilada, servir SPA (Vue Router history mode)
        if ($method === 'GET' && !str_starts_with($uri, '/api/')) {
            $spaFile = __DIR__ . '/../../public/dist/index.html';
            if (file_exists($spaFile)) {
                header('Content-Type: text/html; charset=utf-8');
                readfile($spaFile);
                exit;
            }
        }

        // Si no coincide ninguna ruta
        http_response_code(404);
        if (str_starts_with($uri, '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint no encontrado (404)']);
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
        }
    }

    private function executeHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            $ref = new \ReflectionFunction(\Closure::fromCallable($handler));
            if ($ref->getNumberOfParameters() === 0) {
                $handler();
            } else {
                call_user_func_array($handler, $params);
            }
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler);
            $fullClass = "App\\Controllers\\$controllerClass";

            if (!class_exists($fullClass)) {
                throw new \Exception("Controlador '$fullClass' no encontrado.");
            }

            $controllerInstance = new $fullClass();

            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("Método '$method' no existe en el controlador '$fullClass'.");
            }

            call_user_func_array([$controllerInstance, $method], $params);
            return;
        }

        throw new \Exception("Manejador de ruta inválido.");
    }
}
