<?php

namespace App\Controllers;

use App\Models\Oferta;
use App\Models\OfertaDocumento;
use App\Core\Validator;
use App\Helpers\ConsecutivoHelper;
use Illuminate\Database\Capsule\Manager as Capsule;

class OfertaController extends BaseController
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }



    /**
     * API JSON: Listado de ofertas con filtros y paginación.
     */
    public function apiList(): void
    {
        Oferta::sincronizarEstadosCierre();

        $consecutivo = trim($_GET['consecutivo'] ?? '');
        $objeto      = trim($_GET['objeto'] ?? '');
        $descripcion = trim($_GET['descripcion'] ?? '');
        $actividad   = trim($_GET['actividad'] ?? '');
        $page        = max(1, intval($_GET['page'] ?? 1));
        $perPage     = max(1, min(100, intval($_GET['per_page'] ?? 10)));

        $query = Oferta::with(['actividad', 'documentos'])->orderBy('id', 'desc');

        if ($consecutivo !== '') {
            $query->where('consecutivo', 'LIKE', "%{$consecutivo}%");
        }

        if ($objeto !== '') {
            $query->where('objeto', 'LIKE', "%{$objeto}%");
        }

        if ($descripcion !== '') {
            $query->where('descripcion', 'LIKE', "%{$descripcion}%");
        }

        if ($actividad !== '') {
            $query->whereHas('actividad', function ($q) use ($actividad) {
                $q->where('producto', 'LIKE', "%{$actividad}%")
                  ->orWhere('codigo_producto', 'LIKE', "%{$actividad}%")
                  ->orWhere('segmento', 'LIKE', "%{$actividad}%");
            });
        }

        $total = $query->count();
        $offset = ($page - 1) * $perPage;
        $items = $query->skip($offset)->take($perPage)->get();

        $this->jsonResponse([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => ceil($total / $perPage) ?: 1,
            ]
        ]);
    }



    /**
     * API JSON: Guardar nueva oferta con consecutivo automático.
     */
    public function store(): void
    {
        $data = $this->getRequestData();

        if (!$this->validator->validateOferta($data, false)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Errores de validación en el formulario.',
                'errors'  => $this->validator->getErrors()
            ], 422);
        }

        $maxRetries = 3;
        $oferta = null;
        $consecutivo = '';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $oferta = Capsule::connection()->transaction(function () use ($data, &$consecutivo) {
                    $isMysql = Capsule::connection()->getDriverName() === 'mysql';
                    if ($isMysql) {
                        Capsule::select("SELECT GET_LOCK('oferta_consecutivo_lock', 10)");
                    }

                    try {
                        // Generar consecutivo dentro de la transacción con bloqueo pesimista
                        $consecutivo = ConsecutivoHelper::generar('PO', true);

                        $estadoInicial = $data['estado'] ?? 'Publicada';
                        $cierreTs = strtotime("{$data['fecha_cierre']} {$data['hora_cierre']}");
                        if ($estadoInicial === 'Publicada' && $cierreTs !== false && $cierreTs <= time()) {
                            $estadoInicial = 'Cerrada';
                        }

                        return Oferta::create([
                            'consecutivo'   => $consecutivo,
                            'objeto'        => trim($data['objeto']),
                            'descripcion'   => trim($data['descripcion']),
                            'moneda'        => strtoupper(trim($data['moneda'])),
                            'presupuesto'   => floatval($data['presupuesto']),
                            'actividad_id'  => intval($data['actividad_id']),
                            'fecha_inicio'  => $data['fecha_inicio'],
                            'hora_inicio'   => $data['hora_inicio'],
                            'fecha_cierre'  => $data['fecha_cierre'],
                            'hora_cierre'   => $data['hora_cierre'],
                            'estado'        => $estadoInicial,
                        ]);
                    } finally {
                        if ($isMysql) {
                            Capsule::select("SELECT RELEASE_LOCK('oferta_consecutivo_lock')");
                        }
                    }
                });

                $this->jsonResponse([
                    'success' => true,
                    'message' => "Oferta creada exitosamente con consecutivo {$consecutivo}.",
                    'data'    => $oferta
                ], 201);
                return;
            } catch (\Throwable $e) {
                // Si es error de clave foránea en actividad_id (1452)
                $isFkError = str_contains($e->getMessage(), '1452') || str_contains($e->getMessage(), 'foreign key constraint fails');
                if ($isFkError) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Errores de validación en el formulario.',
                        'errors'  => [
                            'actividad_id' => 'La actividad seleccionada no existe en el clasificador de bienes y servicios (UNSPSC).'
                        ]
                    ], 422);
                    return;
                }

                // Si es colisión de concurrencia en consecutivo (Duplicate entry) y quedan intentos
                $isDuplicate = str_contains($e->getMessage(), '23000') || str_contains($e->getMessage(), '1062');
                if ($isDuplicate && $attempt < $maxRetries) {
                    usleep(150000); // 150ms
                    continue;
                }

                error_log("Error interno en OfertaController::store: " . $e->getMessage());
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ocurrió un error interno en el servidor al guardar la oferta. Por favor intente más tarde.'
                ], 500);
                return;
            }
        }
    }



    /**
     * API JSON: Obtener datos completos de una oferta por ID.
     */
    public function apiGet(int|string $id): void
    {
        Oferta::sincronizarEstadosCierre();

        $oferta = Oferta::with(['actividad', 'documentos'])->find($id);

        if (!$oferta) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Oferta no encontrada.'
            ], 404);
        }

        $this->jsonResponse([
            'success' => true,
            'data'    => $oferta
        ]);
    }

    /**
     * API JSON: Actualizar oferta (con validación obligatoria de al menos 1 documento en edición).
     */
    public function update(int|string $id): void
    {
        Oferta::sincronizarEstadosCierre();

        $oferta = Oferta::with('documentos')->find($id);

        if (!$oferta) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Oferta no encontrada.'
            ], 404);
        }

        $data = $this->getRequestData();

        // 1. Validar campos básicos y cronograma
        if (!$this->validator->validateOferta($data, true)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Errores de validación en el formulario.',
                'errors'  => $this->validator->getErrors()
            ], 422);
        }

        // 2. REGLA OBLIGATORIA DEL PDF: En edición, debe existir al menos 1 documento cargado.
        $documentosActuales = $oferta->documentos()->count();
        $hayNuevoArchivo = isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK;

        if ($documentosActuales === 0 && !$hayNuevoArchivo) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validación obligatoria: en edición debe existir al menos 1 documento cargado (PDF o ZIP).',
                'errors'  => [
                    'documentos' => 'Debe registrar al menos 1 documento para poder guardar los cambios.'
                ]
            ], 422);
        }

        try {
            // Actualizar campos
            $oferta->objeto       = trim($data['objeto']);
            $oferta->descripcion  = trim($data['descripcion']);
            $oferta->moneda       = strtoupper(trim($data['moneda']));
            $oferta->presupuesto  = floatval($data['presupuesto']);
            $oferta->actividad_id = intval($data['actividad_id']);
            $oferta->fecha_inicio = $data['fecha_inicio'];
            $oferta->hora_inicio  = $data['hora_inicio'];
            $oferta->fecha_cierre = $data['fecha_cierre'];
            $oferta->hora_cierre  = $data['hora_cierre'];
            if (!empty($data['estado'])) {
                $cierreTs = strtotime("{$data['fecha_cierre']} {$data['hora_cierre']}");
                if ($data['estado'] === 'Publicada' && $cierreTs !== false && $cierreTs <= time()) {
                    $oferta->estado = 'Cerrada';
                } else {
                    $oferta->estado = $data['estado'];
                }
            }
            $oferta->save();

            // Si se envió un nuevo documento directamente en esta petición
            if ($hayNuevoArchivo) {
                $docMeta = [
                    'titulo'      => trim($data['doc_titulo'] ?? 'Documento adjunto'),
                    'descripcion' => trim($data['doc_descripcion'] ?? 'Cargado en edición'),
                ];
                DocumentoController::procesarSubida(intval($oferta->id), $_FILES['archivo'], $docMeta);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Oferta actualizada exitosamente.',
                'data'    => $oferta->fresh(['actividad', 'documentos'])
            ]);
        } catch (\Throwable $e) {
            $isFkError = str_contains($e->getMessage(), '1452') || str_contains($e->getMessage(), 'foreign key constraint fails');
            if ($isFkError) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Errores de validación en el formulario.',
                    'errors'  => [
                        'actividad_id' => 'La actividad seleccionada no existe en el clasificador de bienes y servicios (UNSPSC).'
                    ]
                ], 422);
                return;
            }

            error_log("Error interno en OfertaController::update: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Ocurrió un error interno en el servidor al actualizar la oferta.'
            ], 500);
        }
    }
}
