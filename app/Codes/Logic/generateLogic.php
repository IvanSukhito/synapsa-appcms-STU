<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Klinik;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class generateLogic
{
    public function __construct()
    {
    }

    public function generatePdfDiagnosa($getData, $filename = 'Download.pdf')
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);

        $html = new Html();

        $getMedicine = AppointmentDoctorProduct::selectRaw('product_name, product_qty')
            ->where('appointment_doctor_id', $getData->id)->get();

        $getClinic = Klinik::where('id', $getData->klinik_id)->first();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Synapsa')
            ->setLastModifiedBy('Synapsa')
            ->setTitle('Laporan Diagnosa')
            ->setSubject('Laporan Diagnosa')
            ->setDescription('Laporan Diagnosa');

        $sheet = $spreadsheet->getActiveSheet();

        $fullColumn = 11;
        $paragraphColumn = 9;

        $firstRow = 1;

        $column = 2;
        $row = 1;
        $sheet->setCellValueByColumnAndRow($column, $row, 'Dr. Dwi Rosaline Febrina');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);
        $sheet->setCellValueByColumnAndRow($column, $row, 'Dokter Umum');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row++);

        $sheet->setCellValueByColumnAndRow($column, $row, '1/2.102/31.75.08.1005/-1.779.3/e/2016');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);

        $sheet->getStyleByColumnAndRow($column, $firstRow + 1, $column + 4, $row)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00A9A9A9'),
            ),
        ]);

        $row = $firstRow;
        $column += 7;

        $getUrl = 'https://unictive.oss-ap-southeast-5.aliyuncs.com/synapsaklinik/uploads/logo/JRyRXJATtXC8FF9rOmJXpGrMV4ZPjCQ5bNOGQyNf.png';
        if($getClinic) {
            $getUrl = strlen($getClinic->logo) > 0 ? env('OSS_URL') . '/' . $getClinic->logo : 'https://unictive.oss-ap-southeast-5.aliyuncs.com/synapsaklinik/uploads/logo/JRyRXJATtXC8FF9rOmJXpGrMV4ZPjCQ5bNOGQyNf.png';
        }

        $getImage = $getUrl;
        $getExt = explode('.', $getImage);
        $ext = end($getExt);
        $fileImageName = 'uploads/logo' . rand(100, 999) . '.' . $ext;
        file_put_contents('./' . $fileImageName, file_get_contents($getImage));

        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path($fileImageName));
        $drawing->setCoordinates('I' . $row);
        $drawing->setWidth('100');
        $drawing->setHeight('40');
        $drawing->getShadow()->setVisible(true);
        $drawing->setWorksheet($sheet);

        $sheet->getStyleByColumnAndRow($column, $row, $column + 1, $row)->applyFromArray([
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ),
        ]);

        $row += 2;

        $sheet->setCellValueByColumnAndRow($column, $row, 'Agu 02, 2021');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);

        $sheet->getStyleByColumnAndRow($column, $row, $column + 1, $row)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00A9A9A9'),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ),
        ]);

        $row += 2;

        $sheet->getStyleByColumnAndRow(1, $row, $fullColumn, $row)->applyFromArray([
            'borders' => array(
                'top' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '00A9A9A9'),
                )
            ),
        ]);

        $row += 2;

        $column = 2;
        $sheet->setCellValueByColumnAndRow($column, $row, 'Nama Pasien:');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $column = 2;
        $sheet->setCellValueByColumnAndRow($column, $row, 'Sherwin');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $sheet->setCellValueByColumnAndRow($column, $row, 'Female, 26 Tahun');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00A9A9A9'),
            ),
        ]);

        $row += 2;

        $sheet->getStyleByColumnAndRow(1, $row, $fullColumn, $row)->applyFromArray([
            'borders' => array(
                'top' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '00A9A9A9'),
                )
            ),
        ]);

        $row += 2;
        $column = 2;

        $sheet->setCellValueByColumnAndRow($column, $row, 'Silahkan menghubungi faskes terdekat jika tidak ada perbaikan');
        $sheet->mergeCellsByColumnAndRow($column, $row, $fullColumn, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $fullColumn, $row)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00A9A9A9'),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ),
        ]);

        $row += 2;

        $column = 2;
        $sheet->setCellValueByColumnAndRow($column, $row, 'Lansoprazole 30mg 10 Kapsul');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $column += 7;

        $sheet->setCellValueByColumnAndRow($column, $row, '1 Per Strip');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 1, $row++)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00A9A9A9'),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ),
        ]);

        $column = 2;
        $sheet->setCellValueByColumnAndRow($column, $row, '2 x 1.0 Kapsul kali per hari');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $sheet->setCellValueByColumnAndRow($column, $row, 'Sebelum makan');
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 12,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $sheet->setCellValueByColumnAndRow($column, $row, $html->toRichTextObject('<font color="#000000">Waktu : </font><font color="#A9A9A9">Pagi, Malam</font>'));
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row++);

        $sheet->setCellValueByColumnAndRow($column, $row, $html->toRichTextObject('<font color="#000000">Catatan : </font><font color="#A9A9A9">20mnt sblm makan ...lambung</font>'));
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);

        $row += 2;

        $sheet->getStyleByColumnAndRow(1, $row, $fullColumn, $row)->applyFromArray([
            'borders' => array(
                'top' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '00A9A9A9'),
                )
            ),
        ]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save('php://output');

        unlink(public_path($fileImageName));

        return true;
    }

}
