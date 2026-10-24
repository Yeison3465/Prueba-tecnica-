<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Validator;

$validator = new Validator();

echo "=== INICIANDO PRUEBAS DE VALIDACIÓN DE REGLAS DE NEGOCIO ===\n\n";

// Caso 1: Datos válidos
$datosValidos = [
    'objeto'       => 'Adquisición de licencias de software',
    'descripcion'  => 'Contrato para provisión y soporte de licencias en la nube durante 12 meses.',
    'moneda'       => 'COP',
    'presupuesto'  => '45000000.00',
    'actividad_id' => 10,
    'fecha_inicio' => '2026-11-01',
    'hora_inicio'  => '08:00',
    'fecha_cierre' => '2026-11-15',
    'hora_cierre'  => '17:00'
];

$pass1 = $validator->validateOferta($datosValidos);
echo "[TEST 1] Datos válidos: " . ($pass1 ? "PASS (Aprobado)" : "FAIL (" . json_encode($validator->getErrors()) . ")") . "\n";

// Caso 2: Objeto vacío o solo espacios
$datosInvalidos1 = $datosValidos;
$datosInvalidos1['objeto'] = '    ';
$pass2 = $validator->validateOferta($datosInvalidos1);
echo "[TEST 2] Objeto solo espacios: " . (!$pass2 && isset($validator->getErrors()['objeto']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 3: Objeto excede 150 caracteres
$datosInvalidos2 = $datosValidos;
$datosInvalidos2['objeto'] = str_repeat('A', 151);
$pass3 = $validator->validateOferta($datosInvalidos2);
echo "[TEST 3] Objeto > 150 caracteres: " . (!$pass3 && isset($validator->getErrors()['objeto']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 4: Descripción excede 400 caracteres
$datosInvalidos3 = $datosValidos;
$datosInvalidos3['descripcion'] = str_repeat('B', 401);
$pass4 = $validator->validateOferta($datosInvalidos3);
echo "[TEST 4] Descripción > 400 caracteres: " . (!$pass4 && isset($validator->getErrors()['descripcion']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 5: Moneda no permitida (ej. JPY)
$datosInvalidos4 = $datosValidos;
$datosInvalidos4['moneda'] = 'JPY';
$pass5 = $validator->validateOferta($datosInvalidos4);
echo "[TEST 5] Moneda inválida (JPY): " . (!$pass5 && isset($validator->getErrors()['moneda']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 6: Presupuesto menor o igual a 0
$datosInvalidos5 = $datosValidos;
$datosInvalidos5['presupuesto'] = '0.00';
$pass6 = $validator->validateOferta($datosInvalidos5);
echo "[TEST 6] Presupuesto = 0: " . (!$pass6 && isset($validator->getErrors()['presupuesto']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 7: Presupuesto con más de 2 decimales
$datosInvalidos6 = $datosValidos;
$datosInvalidos6['presupuesto'] = '100.555';
$pass7 = $validator->validateOferta($datosInvalidos6);
echo "[TEST 7] Presupuesto con 3 decimales: " . (!$pass7 && isset($validator->getErrors()['presupuesto']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 8: Cronograma: fecha_cierre menor a fecha_inicio
$datosInvalidos7 = $datosValidos;
$datosInvalidos7['fecha_cierre'] = '2026-10-20';
$pass8 = $validator->validateOferta($datosInvalidos7);
echo "[TEST 8] Fecha cierre anterior a fecha inicio: " . (!$pass8 && isset($validator->getErrors()['fecha_cierre']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 9: Cronograma: misma fecha pero hora_cierre menor o igual a hora_inicio
$datosInvalidos8 = $datosValidos;
$datosInvalidos8['fecha_cierre'] = '2026-11-01';
$datosInvalidos8['hora_cierre']  = '08:00';
$pass9 = $validator->validateOferta($datosInvalidos8);
echo "[TEST 9] Mismo día con hora cierre <= hora inicio: " . (!$pass9 && isset($validator->getErrors()['hora_cierre']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 10: Validación de archivo no PDF/ZIP (ej. ejecutable .exe o .txt)
$fileInvalido = [
    'name'     => 'script.exe',
    'tmp_name' => __FILE__,
    'size'     => 1024,
    'error'    => UPLOAD_ERR_OK,
];
$metaDoc = ['titulo' => 'Doc prueba', 'descripcion' => 'Desc prueba'];
$pass10 = $validator->validateDocumento($fileInvalido, $metaDoc);
echo "[TEST 10] Archivo no PDF/ZIP (.exe): " . (!$pass10 && isset($validator->getErrors()['archivo']) ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Caso 11: Validación de archivo PDF válido
$fileValido = [
    'name'     => 'pliego_condiciones.pdf',
    'tmp_name' => __FILE__,
    'size'     => 1024 * 50,
    'error'    => UPLOAD_ERR_OK,
];
$pass11 = $validator->validateDocumento($fileValido, $metaDoc);
echo "[TEST 11] Archivo PDF válido: " . ($pass11 ? "PASS (Aprobado)" : "FAIL (" . json_encode($validator->getErrors()) . ")") . "\n";

echo "\n=== TODAS LAS PRUEBAS DE VALIDACIÓN FINALIZADAS ===\n";
