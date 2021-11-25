<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Users;
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

        $getAppointment = AppointmentDoctor::selectRaw('appointment_doctor.*, klinik.logo as logo, doctor_category.name as category')
                            ->leftJoin('doctor','doctor.id','=','appointment_doctor.doctor_id')
                            ->leftJoin('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                            ->leftJoin('klinik','klinik.id','=','appointment_doctor.klinik_id')
                            ->where('appointment_doctor.id', $getData->id)
                            ->first();


        $userLogic = new UserLogic();

        $getUser = $userLogic->userInfo($getData->user_id);

        $tglLahir = date("Y", strtotime($getUser['dob']));

        $dateNow = date("Y-m-d");

        $usia = date("Y", strtotime($dateNow)) - $tglLahir;

        $listSetGender = get_list_gender();
        $listSetTypeDosis = get_list_type_dosis();

        $getMedicine = AppointmentDoctorProduct::selectRaw('product_name, product_qty')
            ->where('appointment_doctor_id', $getData->id)->get();

        //dd($getMedicine);

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
        $sheet->setCellValueByColumnAndRow($column, $row, $getAppointment->doctor_name);
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);
        $sheet->setCellValueByColumnAndRow($column, $row, $getAppointment->category);
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row++);

        $sheet->setCellValueByColumnAndRow($column, $row, $getAppointment->code);
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

        $sheet->setCellValueByColumnAndRow($column, $row, $getAppointment->date);
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
        $sheet->setCellValueByColumnAndRow($column, $row, $getUser['fullname']);
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
        $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
            'font' => array(
                'size' => 14,
                'color' => array('argb' => '00000000'),
            ),
        ]);

        $sheet->setCellValueByColumnAndRow($column, $row, $listSetGender[$getUser['gender']].','.' '.$usia.' '.'Tahun');
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

        if($getMedicine) {



            foreach ($getMedicine as $index => $list ){

                $column = 2;
                $sheet->setCellValueByColumnAndRow($column, $row, $list->product_name. $list->product_qty_checkout);
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
                $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
                    'font' => array(
                        'size' => 14,
                        'color' => array('argb' => '00000000'),
                    ),
                ]);


                $column = 2;
                $sheet->setCellValueByColumnAndRow($column, $row, $list->dosis ?? 'kosong');
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
                $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
                    'font' => array(
                        'size' => 14,
                        'color' => array('argb' => '00000000'),
                    ),
                ]);

                $sheet->setCellValueByColumnAndRow($column, $row, $listSetTypeDosis[$list->type_dosis] ?? 'Kosong');
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);
                $sheet->getStyleByColumnAndRow($column, $row, $column + 4, $row++)->applyFromArray([
                    'font' => array(
                        'size' => 12,
                        'color' => array('argb' => '00000000'),
                    ),
                ]);

                $period = $list->period ?? 'Kosong';
                $note = $list->note ?? 'Kosong';

                $sheet->setCellValueByColumnAndRow($column, $row, $html->toRichTextObject('<font color="#000000">Waktu : </font><font color="#A9A9A9">'.$period.'</font>'));
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row++);

                $sheet->setCellValueByColumnAndRow($column, $row, $html->toRichTextObject('<font color="#000000">Catatan : </font><font color="#A9A9A9">'.$note.'</font>'));
                $sheet->mergeCellsByColumnAndRow($column, $row, $column + 4, $row);


            }

        }

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
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save('php://output');

        unlink(public_path($fileImageName));

        return true;
    }

}
