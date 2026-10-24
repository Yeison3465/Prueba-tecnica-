<?php

namespace App\Helpers;

use App\Models\Oferta;

class ConsecutivoHelper
{
    /**
     * Genera automáticamente el consecutivo con el formato:
     * PO-{3 ceros a la izquierda}{incremental}-{añoEn2Digitos}
     * Ejemplo: PO-0001-26 o O-0001-25
     */
    public static function generar(string $prefijo = 'PO'): string
    {
        $year = date('y'); // Año actual en 2 dígitos, ej. "26"

        // Buscar el último registro cuyo consecutivo termine en el año actual
        $ultimo = Oferta::where('consecutivo', 'LIKE', "%-{$year}")
            ->orderBy('id', 'desc')
            ->first();

        $incremental = 1;

        if ($ultimo && !empty($ultimo->consecutivo)) {
            // Extraer el número entre los guiones: ej. PO-0042-26 -> 42
            if (preg_match('/^(?:PO|O)-(\d+)-\d{2}$/i', trim($ultimo->consecutivo), $matches)) {
                $incremental = intval($matches[1]) + 1;
            }
        }

        return sprintf("%s-%04d-%s", $prefijo, $incremental, $year);
    }
}
