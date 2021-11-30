<?php

namespace App\Codes\Logic;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\AppointmentNurse;
use App\Codes\Models\V1\BookNurse;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SynapsaLogic
{
    public function __construct()
    {
    }

    /**
     * @param $paymentId
     * @return array
     */
    public function checkPayment($paymentId): array
    {
        $getPayment = Payment::where('id', $paymentId)->first();
        if (!$getPayment) {
            return [
                'success' => 0,
                'message' => 'payment not found'
            ];
        }
        else if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
            return [
                'success' => 1,
                'payment' => $getPayment,
                'phone' => 1
            ];
        }
        else {
            return [
                'success' => 1,
                'payment' => $getPayment,
                'phone' => 0
            ];
        }
    }

    /**
     * @Deprecated
     *
     * @param $userId
     * @param null $phone
     * @return array
     */
    public function getUserAddress($userId, $phone = null)
    {
        if ($phone == null) {
            $user = Users::where('id', $userId)->first();
            if ($user) {
                $phone = $user->phone;
            }
        }

        $getUserAddress = UsersAddress::firstOrCreate([
            'user_id' => $userId
        ]);
        if ($getUserAddress) {
            return [
                'address_name' => $getUserAddress->address_name ?? '',
                'phone' => $phone ?? '',
                'province_id' => $getUserAddress->province_id ?? '',
                'province_name' => $getUserAddress->province_name ?? '',
                'city_id' => $getUserAddress->city_id ?? '',
                'city_name' => $getUserAddress->city_name ?? '',
                'district_id' => $getUserAddress->district_id ?? '',
                'district_name' => $getUserAddress->district_name ?? '',
                'sub_district_id' => $getUserAddress->sub_district_id ?? '',
                'sub_district_name' => $getUserAddress->sub_district_name ?? '',
                'address' => $getUserAddress->address ?? '',
                'address_detail' => $getUserAddress->address_detail ?? '',
                'zip_code' => $getUserAddress->zip_code ?? '',
            ];
        }
        else {
            return [];
        }

    }


    public function createPayment($payment, $additional, $transactionId = 0)
    {
        $preferId = 0;
        $success = 0;
        $message = 'OK';
        $getInfo = [];
        if ($payment->service == 'xendit') {
            $typePaymentInfo = '';
            if (substr($payment->type_payment, 0, 3) == 'va_') {
                $typePaymentInfo = 'Virtual Account';
            }
            else if (in_array($payment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
                $typePaymentInfo = 'E-Wallet';
            }
            else if ($payment->type_payment == 'qr_qris') {
                $typePaymentInfo = 'QR Qris';
            }
            else {
                $message = 'Payment Failed';
            }
            $getData = (object)$this->sendPaymentXendit($payment, $additional);
            if ($getData->success == 1) {
                if ($typePaymentInfo == 'Virtual Account' && $getData->result->status == 'PENDING') {
                    $success = 1;
                }
                else if ($typePaymentInfo == 'E-Wallet' && isset($getData->result->ewallet_type) &&
                    in_array($getData->result->ewallet_type, ['OVO', 'DANA']) ||
                    $getData->result->status == 'REQUEST_RECEIVED') {
                    $success = 1;
                }
                else if ($typePaymentInfo == 'QR Qris') {
                    $success = 1;
                }
                else {
                    $message = $getData->result->message;
                }

                if ($success == 1) {
                    $preferId = $getData->result->external_id ?? '';

                    $getInfo = [
                        'price' => $additional['total'],
                        'price_nice' => number_format_local($additional['total']),
                        'external_id' => $preferId,
                        'type' => $typePaymentInfo
                    ];

                    if ($typePaymentInfo == 'Virtual Account') {
                        $getInfo['va_number'] = $getData->result->account_number;
                        $getInfo['va_name'] = $payment->name;
                        $getInfo['va_payment_image'] = $payment->icon_img_full;
                        $getInfo['va_user'] = $getData->result->account_number;
                        $getInfo['va_info'] = json_decode($payment->settings, true);
                    }
                    else if($typePaymentInfo == 'E-Wallet') {
                        $getInfo['business_id'] = $getData->result->business_id ?? '';
                        $getInfo['ewallet_type'] = $getData->result->ewallet_type ?? '';
                        $getInfo['phone'] = $getData->result->phone ?? '';
                        $getInfo['checkout_url'] = $getData->result->checkout_url ?? '';
                        $getInfo['ewallet_name'] = $payment->name;
                        $getInfo['ewallet_payment_image'] = $payment->icon_img_full;
                        $getInfo['ewallet_info'] = json_decode($payment->settings, true);
                        $getInfo['ewallet_return'] = $getData->result;
                    }
                    else if($typePaymentInfo == 'QR Qris') {
                        $getInfo['id'] = $getData->result->id;
                        $getInfo['qr_string'] = $getData->result->qr_string;
                        $getInfo['callback_url'] = $getData->result->callback_url;
                        $getInfo['qris_name'] = $payment->name;
                        $getInfo['qris_payment_image'] = $payment->icon_img_full;
                        $getInfo['qris_info'] = json_decode($payment->settings, true);
                        $getInfo['qris_result'] = $getData->result;
                    }
                }

                if ($transactionId <= 0) {

                    $getTypeService = $additional['job']['type_service'];
                    $getType = check_list_type_transaction($getTypeService);

                    $job = SetJob::create([
                        'status' => 1,
                        'params' => json_encode([
                            'payment_refer_id' => $preferId,
                            'type' => $getType,
                            'payment_info' => $getInfo,
                            'additional' => $additional
                        ])
                    ]);

                    dispatch((new ProcessTransaction($job->id))->onQueue('high'));
                }
                else {
                    $getTransaction = Transaction::where('id', '=', $transactionId)->where('status', '=', 2)->first();
                    if ($getTransaction) {
                        $code = date('Ym').$additional['code'];
                        $getTransaction->code = $code;
                        $getTransaction->payment_id = $payment->id;
                        $getTransaction->payment_name = $payment->name;
                        $getTransaction->payment_service = $payment->service;
                        $getTransaction->type_payment = $payment->type_payment;
                        $getTransaction->payment_refer_id = $preferId;
                        $getTransaction->payment_info = json_encode($getInfo);
                        $getTransaction->send_info = json_encode($additional);
                        $getTransaction->save();
                    }
                    else {
                        $success = 0;
                        $message = 'Transaction not found';
                    }
                }

            }
            else {
                $message = $getData->message;
            }
        }
        else {
            $message = 'Payment Error';
        }

        return [
            'success' => $success,
            'info' => $getInfo,
            'transaction_id' => $transactionId,
            'prefer_id' => $preferId,
            'message' => $message
        ];

    }

    public function sendPaymentXendit($payment, $additional)
    {
        $message = 'Error';
        if ($payment->service == 'xendit') {
            if (!isset($additional['code']) || !isset($additional['total'])) {
                return [
                    'success' => 0,
                    'message' => 'Need Code and Total'
                ];
            }
            else if (substr($payment->type_payment, 0, 3) == 'va_' && !isset($additional['name'])) {
                return [
                    'success' => 0,
                    'message' => 'Need Name'
                ];
            }
            else if (in_array($payment->type_payment, ['ew_ovo', 'ew_linkaja']) && !isset($additional['phone'])) {
                return [
                    'success' => 0,
                    'message' => 'Need Phone'
                ];
            }

            $getCode = $additional['code'] ?? '';
            $getTotal = $additional['total'] ?? '';
            $getName = $additional['name'] ?? '';
            $getPhone = isset($additional['phone']) ? '0'.$additional['phone'] : '';

            $xendit = new XenditLogic();
            $result = false;
            $params = [];
            switch ($payment->type_payment) {
                case 'va_bca':
                case 'va_bri':
                case 'va_bni':
                case 'va_bjb':
                case 'va_cimb':
                case 'va_mandiri':
                case 'va_permata':
                    $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                    $params = [
                        'external_id' => "va-fix-".$getCode,
                        'bank_code' => $getTypePayment,
                        'name' => $getName,
                        'expected_amount' => $getTotal,
                        'is_closed' => true,
                        'is_single_use' => true
                    ];
                    $result = $xendit->createVA($params);
                    break;
                case 'ew_ovo':
                    $params = [
                        'external_id' => 'ew-'.$getCode,
                        'currency' => 'IDR',
                        'amount' => $getTotal,
                        'phone' => $getPhone,
                        'checkout_method' => 'ONE_TIME_PAYMENT',
                        'channel_code' => 'OVO',
                        'ewallet_type' => 'OVO',
                        'channel_properties' => [
                            'mobile_number' => $getPhone,
                            'success_redirect_url' => route('api.postTransactionResult'),
                        ],
                        'metadata' => [
                            'branch_code' => 'tree_branch'
                        ]
                    ];
                    $result = $xendit->createEWalletOVO($params);
                    break;
                case 'ew_dana':
                    $params = [
                        'external_id' => 'ew-'.$getCode,
                        'currency' => 'IDR',
                        'amount' => $getTotal,
                        'phone' => $getPhone,
                        'checkout_method' => 'ONE_TIME_PAYMENT',
                        'channel_code' => 'DANA',
                        'ewallet_type' => 'DANA',
                        'callback_url' => route('api.postTransactionResult'),
                        'redirect_url' => route('api.postTransactionResult'),
                        'channel_properties' => [
                            'success_redirect_url' => route('api.postTransactionResult'),
                        ],
                        'metadata' => [
                            'branch_code' => 'tree_branch'
                        ]
                    ];
                    $result = $xendit->createEWalletDANA($params);
                    break;
                case 'ew_linkaja':

                    $items = [
                        [
                            'name' => 'Item 1',
                            'quantity' => 1,
                            'price' => $getTotal
                        ]
                    ];
                    $params = [
                        'external_id' => 'ew-'.$getCode,
                        'currency' => 'IDR',
                        'amount' => $getTotal,
                        'phone' => $getPhone,
                        'checkout_method' => 'ONE_TIME_PAYMENT',
                        'channel_code' => 'LINKAJA',
                        'ewallet_type' => 'LINKAJA',
                        'items' => $items,
                        'callback_url' => route('api.postTransactionResult'),
                        'redirect_url' => route('api.postTransactionResult'),
                        'channel_properties' => [
                            'success_redirect_url' => route('api.postTransactionResult'),
                        ],
                        'metadata' => [
                            'branch_code' => 'tree_branch'
                        ]
                    ];
                    $result = $xendit->createEWalletLINKAJA($params);
                    break;
                case 'qr_qris':
                    $params = [
                        'external_id' => 'qr-'.$getCode,
                        'type' => 'DYNAMIC',
                        'amount' => $getTotal,
                        'currency' => 'IDR',
                        // 'callback_url' => 'https://synapsa.kelolain.id/transaction-result',
                        'callback_url' => route('api.postTransactionResult'),
                        'phone' => $getPhone
                    ];
                    $result = $xendit->createQrQris($params);
                    break;
            }

            if ($result) {
                $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                $getLogId = 0;
                try {
                    $external_id = isset($result->external_id) ? $result->external_id : '';
                    $getLogId = LogServiceTransaction::create([
                        'transaction_refer_id' => $external_id,
                        'service' => 'xendit',
                        'type_payment' => $getTypePayment,
                        'type_transaction' => 'create',
                        'params' => json_encode($params),
                        'results' => json_encode($result)
                    ]);
                }
                catch (\Exception $e) {
                    Log::info($e->getMessage());
                }

                return [
                    'success' => 1,
                    'result' => (object)$result,
                    'message' => 'Success',
                    'log_id' => $getLogId ? $getLogId->id : 0
                ];
            }

        }

        return [
            'success' => 0,
            'message' => $message
        ];

    }

    public function setupAppointmentNurse($getTransaction, $nurseId, $transactionId, $flag = true)
    {
        $getNurse = BookNurse::where('id', $nurseId)->first();
        if (!$getNurse) {
            return false;
        }

        $getUser = Users::where('id', $getTransaction->user_id)->first();

        if ($flag) {
            DB::beginTransaction();

            $getAppointmentNurse = AppointmentNurse::create([
                'transaction_id' => $getTransaction->id,
                'klinik_id' => $getTransaction->klinik_id,
                'schedule_id' => $getNurse->id,
                'service_id' => 0,
                'user_id' => $getTransaction->user_id,
                'patient_name' => $getUser ? $getUser->fullname : '',
                'patient_email' => $getUser ? $getUser->email : '',
                'type_appointment' => 'nurse',
                'date' => $getNurse->date_booked,
                'shift_qty' => $getNurse->shift_qty,
                'status' => 1
            ]);

            DB::commit();

        }

        return true;


    }

    public function autoCompleteMeeting()
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $getTimeMeeting = intval($setting['time-online-meeting']) ?? 30;
        $timeFinish = date('Y-m-d H:i:s', (strtotime("now") - (60*$getTimeMeeting)));
        AppointmentDoctor::where('time_start_meeting', '<', $timeFinish)->update([
            'online_meeting' => 80
        ]);
        return 1;
    }

    public function autoExpiredTransaction()
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $getExpiredTransaction = intval($setting['time-expired-transaction']) ?? 1;
        $timeFinish = date('Y-m-d H:i:s', strtotime("-$getExpiredTransaction day"));
        $getTransactionIds = Transaction::where('status', 2)->where('created_at', '<', $timeFinish)->pluck('id')->toArray();
        if (count($getTransactionIds) > 0) {
            $doctorLogic = new DoctorLogic();
            $doctorLogic->appointmentReject($getTransactionIds);
            Transaction::where('status', 2)->where('created_at', '<', $timeFinish)->update([
                'status' => 99
            ]);
        }
        return 1;
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
