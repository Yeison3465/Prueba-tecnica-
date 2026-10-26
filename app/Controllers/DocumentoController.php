<?php

namespace App\Controllers;

use App\Models\Oferta;
use App\Models\OfertaDocumento;
use App\Core\Validator;

class DocumentoController extends BaseController
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * API JSON: Subir documento a una oferta existente (vía Modal con Axios).
     */
    public function upload(int|string $id): void
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
            $doc = self::procesarSubida(intval($oferta->id), $_FILES['archivo'], $meta);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Documento agregado correctamente.',
                'data'    => $doc
            ], 201);
        } catch (\Throwable $e) {
            error_log("Error interno en DocumentoController::upload: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Ocurrió un error interno en el servidor al procesar el archivo adjunto.'
            ], 500);
        }
    }

    /**
     * API JSON: Eliminar un documento adjunto.
     */
    public function delete(int|string $id, int|string $docId): void
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
    public function download(int|string $id, int|string $docId): void
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
    public static function procesarSubida(int $ofertaId, array $file, array $meta): OfertaDocumento
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
