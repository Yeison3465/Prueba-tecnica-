<?php

namespace App\Controllers;

use App\Models\Actividad;

class ActividadController extends BaseController
{
    /**
     * Retorna lista de actividades UNSPSC en JSON para poblar selects dinámicos vía Axios.
     */
    public function index(): void
    {
        $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
        $query = Actividad::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('producto', 'LIKE', "%{$search}%")
                  ->orWhere('segmento', 'LIKE', "%{$search}%")
                  ->orWhere('familia', 'LIKE', "%{$search}%")
                  ->orWhere('clase', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_producto', 'LIKE', "%{$search}%");
            });
        }

        // Limitar a 300 registros para mantener la respuesta ágil
        $actividades = $query->limit(300)->get([
            'id',
            'codigo_segmento',
            'segmento',
            'codigo_familia',
            'familia',
            'codigo_clase',
            'clase',
            'codigo_producto',
            'producto'
        ]);

        $this->jsonResponse([
            'success' => true,
            'count' => $actividades->count(),
            'data' => $actividades
        ]);
    }

    /**
     * Retorna el detalle de una actividad específica.
     */
    public function show(int|string $id): void
    {
        $actividad = Actividad::find($id);

        if (!$actividad) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Actividad no encontrada.'
            ], 404);
        }

        $this->jsonResponse([
            'success' => true,
            'data' => $actividad
        ]);
    }
}
