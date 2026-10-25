<?php

namespace App\Core;

use App\Models\Actividad;

class Validator
{
    private array $errors = [];

    public function validateOferta(array $data, bool $isUpdate = false): bool
    {
        $this->errors = [];

        // 1. Objeto: Obligatorio, no solo espacios, máx 150 caracteres
        $objeto = trim($data['objeto'] ?? '');
        if ($objeto === '') {
            $this->errors['objeto'] = 'El campo "Objeto" es obligatorio y no puede contener solo espacios.';
        } elseif (mb_strlen($objeto) > 150) {
            $this->errors['objeto'] = 'El campo "Objeto" no puede exceder los 150 caracteres.';
        }

        // 2. Descripción / Alcance: Obligatorio, no solo espacios, máx 400 caracteres
        $descripcion = trim($data['descripcion'] ?? '');
        if ($descripcion === '') {
            $this->errors['descripcion'] = 'El campo "Descripción / Alcance" es obligatorio y no puede contener solo espacios.';
        } elseif (mb_strlen($descripcion) > 400) {
            $this->errors['descripcion'] = 'El campo "Descripción / Alcance" no puede exceder los 400 caracteres.';
        }

        // 3. Moneda: Obligatorio, solo permite COP, USD, EUR
        $moneda = strtoupper(trim($data['moneda'] ?? ''));
        if (!in_array($moneda, ['COP', 'USD', 'EUR'], true)) {
            $this->errors['moneda'] = 'La moneda es obligatoria y debe ser COP, USD o EUR.';
        }

        // 4. Presupuesto: Mayor a 0, numérico con punto decimal, máx 2 decimales
        $presupuesto = trim($data['presupuesto'] ?? '');
        if ($presupuesto === '' || !is_numeric($presupuesto)) {
            $this->errors['presupuesto'] = 'El presupuesto es obligatorio y debe ser un valor numérico.';
        } else {
            $presupuestoFloat = floatval($presupuesto);
            if ($presupuestoFloat <= 0) {
                $this->errors['presupuesto'] = 'El presupuesto debe ser mayor a 0.';
            } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $presupuesto)) {
                $this->errors['presupuesto'] = 'El presupuesto solo permite números y punto decimal con máximo 2 decimales.';
            }
        }

        // 5. Actividad: Obligatorio, ID numérico y debe existir en base de datos
        $actividadId = $data['actividad_id'] ?? null;
        if (empty($actividadId) || !is_numeric($actividadId)) {
            $this->errors['actividad_id'] = 'Debe seleccionar una actividad válida del clasificador de bienes y servicios.';
        } else {
            try {
                Database::init();
                if (!Actividad::where('id', intval($actividadId))->exists()) {
                    $this->errors['actividad_id'] = 'La actividad seleccionada no existe en el clasificador de bienes y servicios (UNSPSC).';
                }
            } catch (\Throwable $e) {
                $this->errors['actividad_id'] = 'No se pudo verificar la existencia de la actividad seleccionada.';
            }
        }

        // 6. Fechas y Horas (Cronograma)
        $fechaInicio = trim($data['fecha_inicio'] ?? '');
        $horaInicio  = trim($data['hora_inicio'] ?? '');
        $fechaCierre = trim($data['fecha_cierre'] ?? '');
        $horaCierre  = trim($data['hora_cierre'] ?? '');

        if (!$this->isValidDate($fechaInicio)) {
            $this->errors['fecha_inicio'] = 'La fecha de inicio es obligatoria y debe tener formato válido (YYYY-MM-DD).';
        }

        if (!$this->isValidTime($horaInicio)) {
            $this->errors['hora_inicio'] = 'La hora de inicio es obligatoria y debe tener formato válido 24h (HH:mm).';
        }

        if (!$this->isValidDate($fechaCierre)) {
            $this->errors['fecha_cierre'] = 'La fecha de cierre es obligatoria y debe tener formato válido (YYYY-MM-DD).';
        }

        if (!$this->isValidTime($horaCierre)) {
            $this->errors['hora_cierre'] = 'La hora de cierre es obligatoria y debe tener formato válido 24h (HH:mm).';
        }

        // Validación cronológica cruzada
        if (empty($this->errors['fecha_inicio']) && empty($this->errors['fecha_cierre']) &&
            empty($this->errors['hora_inicio']) && empty($this->errors['hora_cierre'])) {
            
            $inicioTimestamp = strtotime("$fechaInicio $horaInicio");
            $cierreTimestamp = strtotime("$fechaCierre $horaCierre");

            if ($fechaCierre < $fechaInicio) {
                $this->errors['fecha_cierre'] = 'La fecha de cierre no puede ser menor a la fecha de inicio.';
            } elseif ($fechaInicio === $fechaCierre && $horaCierre <= $horaInicio) {
                $this->errors['hora_cierre'] = 'Si la fecha de cierre es igual a la fecha de inicio, la hora de cierre debe ser mayor a la hora de inicio.';
            } elseif ($cierreTimestamp <= $inicioTimestamp) {
                $this->errors['hora_cierre'] = 'La fecha y hora de cierre deben ser posteriores a la fecha y hora de inicio.';
            } elseif (!$isUpdate && $cierreTimestamp <= time()) {
                $this->errors['hora_cierre'] = 'La fecha y hora de cierre deben ser posteriores al momento actual.';
            }
        }

        return empty($this->errors);
    }

    public function validateDocumento(array $fileData, array $metaData): bool
    {
        $this->errors = [];

        $titulo = trim($metaData['titulo'] ?? '');
        if ($titulo === '') {
            $this->errors['titulo'] = 'El título del documento es obligatorio.';
        } elseif (mb_strlen($titulo) > 100) {
            $this->errors['titulo'] = 'El título no puede exceder 100 caracteres.';
        }

        $descripcion = trim($metaData['descripcion'] ?? '');
        if ($descripcion === '') {
            $this->errors['descripcion'] = 'La descripción del documento es obligatoria.';
        } elseif (mb_strlen($descripcion) > 200) {
            $this->errors['descripcion'] = 'La descripción no puede exceder 200 caracteres.';
        }

        if (!isset($fileData['tmp_name']) || empty($fileData['tmp_name']) || $fileData['error'] !== UPLOAD_ERR_OK) {
            $this->errors['archivo'] = 'Debe seleccionar un archivo válido.';
            return false;
        }

        $tmpPath = $fileData['tmp_name'];
        if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
            $this->errors['archivo'] = 'No se puede acceder al archivo temporal cargado.';
            return false;
        }

        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'zip'];

        if (!in_array($extension, $allowedExtensions, true)) {
            $this->errors['archivo'] = 'Formato no permitido. Solo se permiten archivos PDF o ZIP.';
            return false;
        }

        // Inspección de Magic Bytes (Firmas binarias de cabecera)
        $handle = @fopen($tmpPath, 'rb');
        $headerBytes = $handle ? fread($handle, 8) : '';
        if ($handle) {
            fclose($handle);
        }

        // Detección y bloqueo inmediato de ejecutables DOS/Windows (MZ header)
        if (str_starts_with($headerBytes, "MZ")) {
            $this->errors['archivo'] = 'Archivo no permitido: se detectó un archivo ejecutable binario.';
            return false;
        }

        // Detección y bloqueo inmediato de scripts PHP
        if (str_starts_with($headerBytes, "<?php") || str_starts_with($headerBytes, "<?=")) {
            $this->errors['archivo'] = 'Archivo no permitido: se detectó un script ejecutable.';
            return false;
        }

        // Validación específica por formato mediante Magic Bytes
        if ($extension === 'pdf') {
            if (!str_starts_with($headerBytes, "%PDF")) {
                $this->errors['archivo'] = 'El archivo no es un documento PDF auténtico (cabecera corrupta o inválida).';
                return false;
            }
        } elseif ($extension === 'zip') {
            $validZip = str_starts_with($headerBytes, "PK\x03\x04")
                     || str_starts_with($headerBytes, "PK\x05\x06")
                     || str_starts_with($headerBytes, "PK\x07\x08");
            if (!$validZip) {
                $this->errors['archivo'] = 'El archivo no es un comprimido ZIP auténtico.';
                return false;
            }
        }

        // Inspección de MIME Type real mediante finfo_file
        if (extension_loaded('fileinfo')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
            if ($finfo) {
                finfo_close($finfo);
            }

            if ($mimeType) {
                $allowedPdfMimes = ['application/pdf', 'application/x-pdf'];
                $allowedZipMimes = [
                    'application/zip',
                    'application/x-zip-compressed',
                    'application/x-zip',
                    'multipart/x-zip',
                    'application/octet-stream'
                ];

                if ($extension === 'pdf' && !in_array($mimeType, $allowedPdfMimes, true)) {
                    $this->errors['archivo'] = "Tipo MIME inválido ({$mimeType}). Se esperaba application/pdf.";
                    return false;
                }
                if ($extension === 'zip' && !in_array($mimeType, $allowedZipMimes, true)) {
                    $this->errors['archivo'] = "Tipo MIME inválido ({$mimeType}). Se esperaba application/zip.";
                    return false;
                }
            }
        }

        // Validar tamaño máximo (ej. 20MB)
        $maxSize = 20 * 1024 * 1024;
        if ($fileData['size'] > $maxSize) {
            $this->errors['archivo'] = 'El archivo supera el tamaño máximo permitido de 20 MB.';
            return false;
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidTime(string $time): bool
    {
        return preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/', $time) === 1;
    }
}
