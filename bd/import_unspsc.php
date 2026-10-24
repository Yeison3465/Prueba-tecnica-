<?php

/**
 * Script de importación de la maestra UNSPSC a la base de datos MySQL.
 * Puede ejecutarse desde la terminal: php bd/import_unspsc.php
 * O generar un archivo bd/actividades_seed.sql
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Core\Database;
use Illuminate\Database\Capsule\Manager as Capsule;

$excelFile = __DIR__ . '/unspcs.xlsx';
if (!file_exists($excelFile)) {
    echo "Descargando maestra UNSPSC...\n";
    $url = "https://a.storyblok.com/f/167454/x/8db69f44cd/unspcs-clasificador-de-bienes-y-servicios-de-naciones-unidas-en-espanol.xlsx";
    file_put_contents($excelFile, file_get_contents($url));
}

echo "Abriendo archivo Excel: $excelFile\n";
$reader = IOFactory::createReaderForFile($excelFile);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($excelFile);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = $sheet->getHighestRow();
echo "Total de filas detectadas: $highestRow\n";

// Conectar a BD si está disponible
$dbConnected = false;
try {
    Database::init();
    Capsule::connection()->getPdo();
    $dbConnected = true;
    echo "Conexión a BD establecida correctamente.\n";
} catch (\Throwable $e) {
    echo "Aviso: No se pudo conectar a MySQL (" . $e->getMessage() . "). Se generará únicamente el archivo SQL de seed.\n";
}

$seedFile = __DIR__ . '/actividades_seed.sql';
$fp = fopen($seedFile, 'w');
fwrite($fp, "-- Seed inicial de actividades UNSPSC\nUSE licitaciones_db;\n\n");

$batch = [];
$batchSql = [];
$totalInsertados = 0;
$limit = min($highestRow, 3000); // Muestra robusta de 3000 registros para velocidad y agilidad

for ($row = 7; $row <= $limit; $row++) {
    $rowData = $sheet->rangeToArray("A{$row}:H{$row}")[0];
    
    $codSegmento = intval($rowData[0] ?? 0);
    $segmento    = trim((string)($rowData[1] ?? ''));
    $codFamilia  = intval($rowData[2] ?? 0);
    $familia     = trim((string)($rowData[3] ?? ''));
    $codClase    = intval($rowData[4] ?? 0);
    $clase       = trim((string)($rowData[5] ?? ''));
    $codProducto = intval($rowData[6] ?? 0);
    $producto    = trim((string)($rowData[7] ?? ''));

    if (empty($segmento) || empty($producto)) {
        continue;
    }

    $escapedSegmento = addslashes(mb_substr($segmento, 0, 200));
    $escapedFamilia  = addslashes(mb_substr($familia, 0, 200));
    $escapedClase    = addslashes(mb_substr($clase, 0, 200));
    $escapedProducto = addslashes(mb_substr($producto, 0, 200));

    $batchSql[] = "({$codSegmento}, '{$escapedSegmento}', {$codFamilia}, '{$escapedFamilia}', {$codClase}, '{$escapedClase}', {$codProducto}, '{$escapedProducto}')";

    if ($dbConnected) {
        $batch[] = [
            'codigo_segmento' => $codSegmento,
            'Segmento'        => mb_substr($segmento, 0, 200),
            'codigo_familia'  => $codFamilia,
            'familia'         => mb_substr($familia, 0, 200),
            'codigo_clase'    => $codClase,
            'clase'           => mb_substr($clase, 0, 200),
            'codigo_producto' => $codProducto,
            'producto'        => mb_substr($producto, 0, 200),
        ];
    }

    if (count($batchSql) >= 500) {
        fwrite($fp, "INSERT INTO actividades (codigo_segmento, Segmento, codigo_familia, familia, codigo_clase, clase, codigo_producto, producto) VALUES \n" . implode(",\n", $batchSql) . ";\n\n");
        $batchSql = [];

        if ($dbConnected) {
            Capsule::table('actividades')->insert($batch);
            $batch = [];
        }
        $totalInsertados += 500;
        echo "Procesadas $totalInsertados filas...\n";
    }
}

if (!empty($batchSql)) {
    fwrite($fp, "INSERT INTO actividades (codigo_segmento, Segmento, codigo_familia, familia, codigo_clase, clase, codigo_producto, producto) VALUES \n" . implode(",\n", $batchSql) . ";\n\n");
    $totalInsertados += count($batchSql);
}

if ($dbConnected && !empty($batch)) {
    Capsule::table('actividades')->insert($batch);
}

fclose($fp);
echo "Finalizado con éxito. Total registros generados: $totalInsertados en $seedFile\n";
