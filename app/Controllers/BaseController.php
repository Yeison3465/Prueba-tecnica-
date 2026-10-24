<?php

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Retorna una respuesta en formato JSON con cabeceras adecuadas.
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Renderiza una vista PHP inyectando variables.
     */
    protected function renderView(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullPath = __DIR__ . '/../../views/' . ltrim($viewPath, '/') . '.php';

        if (!file_exists($fullPath)) {
            http_response_code(500);
            die("Error: La vista '{$viewPath}' no existe en la ruta: {$fullPath}");
        }

        require $fullPath;
    }

    /**
     * Redirecciona a una URL interna.
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Obtiene los datos del cuerpo de una petición JSON o POST normal.
     */
    protected function getRequestData(): array
    {
        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');

        if (str_contains($contentType, 'application/json') || str_contains($contentType, 'json')) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (is_array($data)) {
                return $data;
            }
        }

        if (empty($_POST)) {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $data = json_decode($input, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }

        return $_POST;
    }
}
