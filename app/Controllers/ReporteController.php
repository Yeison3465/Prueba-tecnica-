<?php

namespace App\Controllers;

use App\Models\Oferta;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteController extends BaseController
{
    /**
     * Genera y descarga el reporte en formato Excel (.xlsx) con los filtros actuales
     * omitiendo la paginación, tal como lo exige la prueba técnica.
     */
    public function exportarExcel(): void
    {
        $consecutivo = trim($_GET['consecutivo'] ?? '');
        $objeto      = trim($_GET['objeto'] ?? '');
        $descripcion = trim($_GET['descripcion'] ?? '');

        Oferta::sincronizarEstadosCierre();

        $query = Oferta::with('actividad')->orderBy('id', 'desc');

        if ($consecutivo !== '') {
            $query->where('consecutivo', 'LIKE', "%{$consecutivo}%");
        }

        if ($objeto !== '') {
            $query->where('objeto', 'LIKE', "%{$objeto}%");
        }

        if ($descripcion !== '') {
            $query->where('descripcion', 'LIKE', "%{$descripcion}%");
        }

        $ofertas = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Licitaciones');

        // 1. Título principal
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'REPORTE GENERAL DE LICITACIONES Y OFERTAS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A'); // Azul Suplos corporativo
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // Subtítulo con fecha
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Generado el: ' . date('Y-m-d H:i:s') . ' | Total de registros: ' . $ofertas->count());
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Encabezados de tabla
        $headers = [
            'A3' => 'Consecutivo',
            'B3' => 'Objeto',
            'C3' => 'Descripción / Alcance',
            'D3' => 'Actividad UNSPSC',
            'E3' => 'Moneda',
            'F3' => 'Presupuesto',
            'G3' => 'Fecha Inicio',
            'H3' => 'Hora Inicio',
            'I3' => 'Fecha Cierre',
            'J3' => 'Hora Cierre',
            'K3' => 'Estado',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2563EB'], // Azul Suplos
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];

        $sheet->getStyle('A3:K3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // 3. Filas de datos
        $row = 4;
        foreach ($ofertas as $oferta) {
            $actividadTexto = $oferta->actividad 
                ? "{$oferta->actividad->producto} ({$oferta->actividad->codigo_producto})" 
                : 'N/A';

            $sheet->setCellValue("A{$row}", $oferta->consecutivo);
            $sheet->setCellValue("B{$row}", $oferta->objeto);
            $sheet->setCellValue("C{$row}", $oferta->descripcion);
            $sheet->setCellValue("D{$row}", $actividadTexto);
            $sheet->setCellValue("E{$row}", $oferta->moneda);
            $sheet->setCellValue("F{$row}", $oferta->presupuesto);
            $sheet->setCellValue("G{$row}", $oferta->fecha_inicio ? date('Y-m-d', strtotime($oferta->fecha_inicio)) : '');
            $sheet->setCellValue("H{$row}", $oferta->hora_inicio);
            $sheet->setCellValue("I{$row}", $oferta->fecha_cierre ? date('Y-m-d', strtotime($oferta->fecha_cierre)) : '');
            $sheet->setCellValue("J{$row}", $oferta->hora_cierre);
            $sheet->setCellValue("K{$row}", $oferta->estado);

            // Estilos por fila
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Borde y cebra
            $fillColor = ($row % 2 === 0) ? 'FFF8FAFC' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);
            $sheet->getStyle("A{$row}:K{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');

            $row++;
        }

        // Auto-ajustar ancho de columnas
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 4. Salida en streaming como descarga XLSX
        $filename = 'ofertas_reporte_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
