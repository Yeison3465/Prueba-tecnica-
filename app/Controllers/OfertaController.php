<?php

namespace App\Controllers;

use App\Models\Oferta;
use App\Models\OfertaDocumento;
use App\Core\Validator;
use App\Helpers\ConsecutivoHelper;

class OfertaController extends BaseController
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * Vista principal: Listado de ofertas.
     */
    public function index(): void
    {
        $this->renderView('ofertas/index', [
            'pageTitle' => 'Listado de Ofertas'
        ]);
    }

    /**
     * API JSON: Listado de ofertas con filtros y paginación.
     */
    public function apiList(): void
    {
        $consecutivo = trim($_GET['consecutivo'] ?? '');
        $objeto      = trim($_GET['objeto'] ?? '');
        $descripcion = trim($_GET['descripcion'] ?? '');
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
     * Vista: Formulario de Creación de Oferta.
     */
    public function create(): void
    {
        $this->renderView('ofertas/create', [
            'pageTitle' => 'Crear Nueva Oferta'
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

        try {
            // Generar consecutivo automático: PO-0001-26
            $consecutivo = ConsecutivoHelper::generar();

            $oferta = Oferta::create([
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
                'estado'        => $data['estado'] ?? 'Publicada',
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => "Oferta creada exitosamente con consecutivo {$consecutivo}.",
                'data'    => $oferta
            ], 201);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al guardar la oferta en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista: Formulario de Edición de Oferta.
     */
    public function edit(int|string $id): void
    {
        $this->renderView('ofertas/edit', [
            'pageTitle' => 'Editar Oferta',
            'ofertaId'  => intval($id)
        ]);
    }

    /**
     * Vista: Detalle de Oferta organizado por pestañas.
     */
    public function show(int|string $id): void
    {
        $this->renderView('ofertas/show', [
            'pageTitle' => 'Detalle de la Oferta',
            'ofertaId'  => intval($id)
        ]);
    }

    /**
     * API JSON: Obtener datos completos de una oferta por ID.
     */
    public function apiGet(int|string $id): void
    {
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
                $oferta->estado = $data['estado'];
            }
            $oferta->save();

            // Si se envió un nuevo documento directamente en esta petición
            if ($hayNuevoArchivo) {
                $docMeta = [
                    'titulo'      => trim($data['doc_titulo'] ?? 'Documento adjunto'),
                    'descripcion' => trim($data['doc_descripcion'] ?? 'Cargado en edición'),
                ];
                $this->procesarSubidaDocumento($oferta->id, $_FILES['archivo'], $docMeta);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Oferta actualizada exitosamente.',
                'data'    => $oferta->fresh(['actividad', 'documentos'])
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar la oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API JSON: Subir documento a una oferta existente (vía Modal con Axios).
     */
    public function uploadDocumento(int|string $id): void
    {
        $oferta = Oferta::find($id);

        if (!$oferta) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Oferta no encontrada.'
            ], 404);
        }

        $meta = [
            'titulo'      => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if (!isset($_FILES['archivo'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'No se ha enviado ningún archivo.',
                'errors'  => ['archivo' => 'El archivo es requerido.']
            ], 422);
        }

        if (!$this->validator->validateDocumento($_FILES['archivo'], $meta)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'El documento no cumple los requisitos.',
                'errors'  => $this->validator->getErrors()
            ], 422);
        }

        try {
            $doc = $this->procesarSubidaDocumento($oferta->id, $_FILES['archivo'], $meta);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Documento agregado correctamente.',
                'data'    => $doc
            ], 201);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al guardar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API JSON: Eliminar un documento adjunto.
     */
    public function deleteDocumento(int|string $id, int|string $docId): void
    {
        $documento = OfertaDocumento::where('licitacion_id', $id)->where('id', $docId)->first();

        if (!$documento) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Documento no encontrado.'
            ], 404);
        }

        // Eliminar archivo físico
        $filePath = __DIR__ . '/../../public/' . ltrim($documento->archivo, '/');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $documento->delete();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Documento eliminado correctamente.'
        ]);
    }

    /**
     * Descarga de un documento adjunto.
     */
    public function downloadDocumento(int|string $id, int|string $docId): void
    {
        $documento = OfertaDocumento::where('licitacion_id', $id)->where('id', $docId)->first();

        if (!$documento) {
            http_response_code(404);
            die("Documento no encontrado.");
        }

        $filePath = __DIR__ . '/../../public/' . ltrim($documento->archivo, '/');

        if (!file_exists($filePath)) {
            http_response_code(404);
            die("El archivo físico no se encuentra en el servidor.");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = ($extension === 'pdf') ? 'application/pdf' : 'application/zip';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $documento->titulo) . '.' . $extension;

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Procesa y almacena físicamente el archivo PDF/ZIP en /public/uploads/
     */
    private function procesarSubidaDocumento(int $ofertaId, array $file, array $meta): OfertaDocumento
    {
        $uploadsDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = 'doc_' . $ofertaId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destination = $uploadsDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \Exception("No se pudo mover el archivo al directorio de destino.");
        }

        return OfertaDocumento::create([
            'licitacion_id' => $ofertaId,
            'titulo'        => $meta['titulo'],
            'descripcion'   => $meta['descripcion'],
            'archivo'       => 'uploads/' . $safeName,
        ]);
    }
}
