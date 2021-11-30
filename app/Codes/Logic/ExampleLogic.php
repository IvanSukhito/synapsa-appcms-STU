<?php

namespace App\Codes\Logic;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExampleLogic
{
    public function __construct()
    {
    }

    public function downloadExampleImportProduct() {
        $file = env('OSS_URL') . '/' . 'synapsaapps/product/example_import/uB2jJ6dmcFIetHuRnu3EuVjqGRme2H72I2I0jEDP.xlsx';
        $fileName = create_slugs('Example Import Product Clinic');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
        exit;
    }

    public function downloadExampleImportDoctorClinic() {
        $file = env('OSS_URL') . '/' . 'synapsaapps/doctor-clinic/example_import/DhHi3aQeirksGxP5tekhiccI4A4HZviaibflu7Lq.xlsx';
        $fileName = create_slugs('Example Import Doctor Clinic');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
        exit;
    }

    public function downloadExampleImportDoctor() {
        $file = env('OSS_URL') . '/' . 'synapsaapps/doctor/example_import/7dQ5GOyQhnYnODwj1TaPeY5u3hpCs142emXKLzH8.xlsx';
        $fileName = create_slugs('Example Import Doctor Clinic');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
        exit;
    }

    public function downloadExampleImportLabSchedule() {
        $fileName = create_slugs('Example Import Lab Schedule');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Synapsa Klinik')->setLastModifiedBy('Synapsa Klinik');

        $sheet = $spreadsheet->getActiveSheet();
        $column = 1;

        $headers = [
            'nomor' => 'no',
            'service' => 'service',
            'date_available' => 'date_available',
            'weekday' => 'weekday',
            'time_start' => 'time_start',
            'time_end' => 'time_end',
        ];

        foreach ($headers as $key => $value) {
            $row = 6;

            if ($key == 'nomor') {
                $sheet->getColumnDimensionByColumn($column)->setWidth(5);
            }
            else if ($key == 'service') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, '2 = Homecare');
                $sheet->setCellValueByColumnAndRow($column, $row, '3 = Visit');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(25);
                $row += 3;
            }
            else if ($key == 'date_available') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, 'mm-dd-yyyy');
                $sheet->setCellValueByColumnAndRow($column, $row, '(Opsional Untuk Jadwal Khusus)');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row++)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(30);
                $row += 2;
            }
            else if ($key == 'weekday') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Monday');
                $sheet->setCellValueByColumnAndRow($column, $row++,'Senin');
                $sheet->setCellValueByColumnAndRow($column, $row, '(Wajib Untuk Jadwal Normal)');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(30);
                $row += 2;
            }
            else if ($key == 'time_start' || $key == 'time_end') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row, 'Berupa Jam : 08:00');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(25);
                $row += 4;
            }
            else {
                $sheet->getColumnDimensionByColumn($column)->setWidth(20);
            }

            $sheet->setCellValueByColumnAndRow($column, $row, __('general.' . $key));
            $sheet->getStyleByColumnAndRow($column, $row, $column++, $row)->applyFromArray([
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('argb' => '000070C0'),
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => '00000000'),
                    )
                ),
                'alignment' => array(
                    'horizontal' => \Phpoffice\Phpspreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                )
            ]);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function downloadExampleImportDoctorSchedule() {
        $file = env('OSS_URL') . '/' . 'synapsaapps/doctor-schedule/example_import/hvhhdk8v9YonKkZKiBbp5v2okaOf9fxT2VeygitQ.xlsx';
        $fileName = create_slugs('Example Import Doctor Schedule');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
        exit;
    }

    public function downloadExampleImportDoctorClinicSchedule() {
        $fileName = create_slugs('Example Import Doctor Schedule');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Synapsa Klinik')->setLastModifiedBy('Synapsa Klinik');

        $sheet = $spreadsheet->getActiveSheet();
        $column = 1;

        $headers = [
            'nomor' => 'no',
            'service' => 'service',
            'date_available' => 'date_available',
            'weekday' => 'weekday',
            'time_start' => 'time_start',
            'time_end' => 'time_end',
        ];

        foreach ($headers as $key => $value) {
            $row = 6;

            if ($key == 'nomor') {
                $sheet->getColumnDimensionByColumn($column)->setWidth(5);
            }
            else if ($key == 'service') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, '1 = Telemed');
                $sheet->setCellValueByColumnAndRow($column, $row++, '2 = Homecare');
                $sheet->setCellValueByColumnAndRow($column, $row, '3 = Visit');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(25);
                $row += 2;
            }
            else if ($key == 'date_available') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, 'mm-dd-yyyy');
                $sheet->setCellValueByColumnAndRow($column, $row, '(Opsional Untuk Jadwal Khusus)');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row++)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(30);
                $row += 2;
            }
            else if ($key == 'weekday') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Monday');
                $sheet->setCellValueByColumnAndRow($column, $row++,'Senin');
                $sheet->setCellValueByColumnAndRow($column, $row, '(Wajib Untuk Jadwal Normal)');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(30);
                $row += 2;
            }
            else if ($key == 'time_start' || $key == 'time_end') {
                $row = 1;
                $sheet->setCellValueByColumnAndRow($column, $row++, 'Notes:');
                $sheet->setCellValueByColumnAndRow($column, $row, 'Berupa Jam : 08:00');
                $sheet->getStyleByColumnAndRow($column, 1, $column, $row)->applyFromArray([
                    'font' => array(
                        'color' => array('argb' => '00000000'),
                    ),
                    'alignment' => array(
                        'wrapText' => true,
                    )
                ]);
                $sheet->getColumnDimensionByColumn($column)->setWidth(25);
                $row += 4;
            }
            else {
                $sheet->getColumnDimensionByColumn($column)->setWidth(20);
            }

            $sheet->setCellValueByColumnAndRow($column, $row, __('general.' . $key));
            $sheet->getStyleByColumnAndRow($column, $row, $column++, $row)->applyFromArray([
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('argb' => '000070C0'),
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => '00000000'),
                    )
                ),
                'alignment' => array(
                    'horizontal' => \Phpoffice\Phpspreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                )
            ]);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function downloadExampleImportClinic() {
        $file = env('OSS_URL') . '/' . 'synapsaapps/clinic/example_import/l520IsR421gA4AyQm19JH8wkdrsN1hi5VsDFEPMs.xlsx';
        $fileName = create_slugs('Example Import Clinic');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
        exit;
    }

}
