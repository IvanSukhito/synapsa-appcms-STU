<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctorProduct;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class generateLogic
{
    public function __construct()
    {
    }

    public function generatePdfDiagnosa($getData, $filename = 'Download.pdf')
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);

        $diagnosis = $getData->diagnosis;
        $treatment = $getData->treatment;
        $getMedicine = AppointmentDoctorProduct::selectRaw('product_name, product_qty')
            ->where('appointment_doctor_id', $getData->id)->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Synapsa')
            ->setLastModifiedBy('Synapsa')
            ->setTitle('Laporan Diagnosa')
            ->setSubject('Laporan Diagnosa')
            ->setDescription('Laporan Diagnosa');

        $sheet = $spreadsheet->getActiveSheet();

        $column = 1;
        $sheet->getColumnDimensionByColumn($column++)->setWidth(6.00);
        $sheet->getColumnDimensionByColumn($column++)->setWidth(20.00);
        $sheet->getColumnDimensionByColumn($column++)->setWidth(6.00);

        $column = 1;
        $row = 1;
        $sheet->setCellValueByColumnAndRow($column++, $row, 'Diagnosis:');
        $sheet->setCellValueByColumnAndRow($column++, $row, $diagnosis);
        $sheet->mergeCellsByColumnAndRow(2,$row, 3, $row);

        $column = 1;
        $row += 1;
        $sheet->setCellValueByColumnAndRow($column++, $row, 'Treatment:');
        $sheet->setCellValueByColumnAndRow($column++, $row, $treatment);
        $sheet->mergeCellsByColumnAndRow(2,$row, 3, $row);

        if ($getMedicine) {
            $row += 2;
            foreach ($getMedicine as $index => $list) {
                $column = 1;
                $row += 1;
                $sheet->setCellValueByColumnAndRow($column++, $row, ($index+1).'. ');
                $sheet->setCellValueByColumnAndRow($column++, $row, $list->product_name);
                $sheet->setCellValueByColumnAndRow($column++, $row, $list->product_qty);
            }
        }

        $column = 1;
        $row += 3;
        $sheet->setCellValueByColumnAndRow($column++, $row, 'Terima Kasih');
        $sheet->mergeCellsByColumnAndRow(1,$row, 3, $row);


        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        header('Cache-Control: max-age=0');

        IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save('php://output');
        exit;

    }

}
