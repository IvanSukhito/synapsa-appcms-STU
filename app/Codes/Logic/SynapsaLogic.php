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
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SynapsaLogic
{
    public function __construct()
    {
    }

    /**
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

    public function createPayment($payment, $additional, $flag = 0)
    {
        $transactionId = 0;
        $preferId = 0;
        $success = 0;
        $message = 'OK';
        $getInfo = [];
        if ($payment->service == 'xendit' && substr($payment->type_payment, 0, 3) == 'va_') {
            $getData = (object)$this->sendPayment($payment, $additional);
            if ($getData->success == 1) {
                if ($getData->result->status == 'PENDING') {
                    $success = 1;
                    $getInfoVa = json_decode($payment->settings, true);
                    $getInfo['price'] = $additional['total'];
                    $getInfo['price_nice'] = number_format($additional['total'], 0, ',', '.');
                    $getInfo['va_number'] = $getData->result->account_number;
                    $getInfo['va_name'] = $payment->name;
                    $getInfo['va_payment_image'] = $payment->icon_img_full;
                    $getInfo['va_user'] = $getData->result->account_number;
                    $getInfo['va_info'] = $getInfoVa;
                    $preferId = isset($getData->result->external_id) ? $getData->result->external_id : '';

                    if ($flag == 0) {
                        $getTypeService = $additional['job']['type_service'];
                        $getType = check_list_type_transaction($getTypeService);

                        $getJobData = $additional['job'];
                        $getJobData['payment_refer_id'] = $getData->result->external_id;
                        $getJobData['type'] = $getType;
                        $getJobData['payment_info'] = json_encode($getInfo);
                        $getJobData['additional'] = json_encode($additional);

                        $job = SetJob::create([
                            'status' => 1,
                            'params' => json_encode($getJobData)
                        ]);

                        dispatch((new ProcessTransaction($job->id))->onQueue('high'));

                    }

                } else {
                    $message = $getData->result->message;
                }
            }
            else {
                $message = $getData->message;
            }
        }
        else if ($payment->service == 'xendit' && in_array($payment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
            $getData = (object)$this->sendPayment($payment, $additional);
            if ($getData->success == 1) {
                if (isset($getData->result->ewallet_type) && in_array($getData->result->ewallet_type, ['OVO', 'DANA']) || $getData->result->status == 'REQUEST_RECEIVED') {
                    $success = 1;
                    $getInfoWallet = json_decode($payment->settings, true);
                    $getInfo['price'] = $additional['total'];
                    $getInfo['price_nice'] = number_format($additional['total'], 0, ',', '.');
                    $getInfo['business_id'] = $getData->result->business_id ?? '';
                    $getInfo['ewallet_type'] = $getData->result->ewallet_type ?? '';
                    $getInfo['phone'] = $getData->result->phone ?? '';
                    $getInfo['checkout_url'] = $getData->result->checkout_url ?? '';
                    $getInfo['ewallet_name'] = $payment->name;
                    $getInfo['ewallet_payment_image'] = $payment->icon_img_full;
                    $getInfo['ewallet_info'] = $getInfoWallet;
                    $getInfo['ewallet_return'] = $getData->result;
                    $preferId = isset($getData->result->external_id) ? $getData->result->external_id : '';

                    if ($flag == 0) {
                        $getTypeService = $additional['job']['type_service'];
                        $getType = check_list_type_transaction($getTypeService);

                        $getJobData = $additional['job'];
                        $getJobData['payment_refer_id'] = isset($getData->result->external_id) ? $getData->result->external_id : '';
                        $getJobData['type'] = $getType;
                        $getJobData['payment_info'] = json_encode($getInfo);
                        $getJobData['additional'] = json_encode($additional);

                        $job = SetJob::create([
                            'status' => 1,
                            'params' => json_encode($getJobData)
                        ]);

                        dispatch((new ProcessTransaction($job->id))->onQueue('high'));
                    }

                }
                else {
                    $message = $getData->result->message;
                }
            }
            else {
                $message = $getData->message;
            }
        }
        else if ($payment->service == 'xendit' && in_array($payment->type_payment, ['qr_qris'])) {
            $getData = (object)$this->sendPayment($payment, $additional);

            if ($getData->success == 1) {
                $success = 1;
                $getResultQris = $getData->result;
                $getInfoQris = json_decode($payment->settings, true);
                $getInfo['price'] = $additional['total'];
                $getInfo['price_nice'] = number_format($additional['total'], 0, ',', '.');
                $getInfo['id'] = $getData->result->id;
                $getInfo['external_id'] = $getData->result->external_id;
                $getInfo['qr_string'] = $getData->result->qr_string;
                $getInfo['callback_url'] = $getData->result->callback_url;
                $getInfo['qris_name'] = $payment->name;
                $getInfo['qris_payment_image'] = $payment->icon_img_full;
                $getInfo['qris_info'] = $getInfoQris;
                $getInfo['qris_result'] = $getResultQris;
                $preferId = isset($getData->result->external_id) ? $getData->result->external_id : '';

                if ($flag == 0) {
                    $getTypeService = $additional['job']['type_service'];
                    $getType = check_list_type_transaction($getTypeService);

                    $getJobData = $additional['job'];
                    $getJobData['payment_refer_id'] = isset($getData->result->external_id) ? $getData->result->external_id : '';
                    $getJobData['type'] = $getType;
                    $getJobData['additional'] = json_encode($additional);

                    $job = SetJob::create([
                        'status' => 1,
                        'params' => json_encode($getJobData)
                    ]);

                    dispatch((new ProcessTransaction($job->id))->onQueue('high'));
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

    public function sendPayment($payment, $additional)
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
                $getLogId = LogServiceTransaction::create([
                    'transaction_refer_id' => isset($result['external_id']) ? $result['external_id'] : '',
                    'service' => 'xendit',
                    'type_payment' => $getTypePayment,
                    'type_transaction' => 'create',
                    'params' => json_encode($params),
                    'results' => json_encode($result)
                ]);

                return [
                    'success' => 1,
                    'result' => (object)$result,
                    'message' => 'Success',
                    'log_id' => $getLogId->id
                ];
            }

        }

        return [
            'success' => 0,
            'message' => $message
        ];

    }

    public function setupAppointmentDoctor($getTransaction, $getTransactionDetails, $scheduleId, $flag = true)
    {
        $getSchedule = DoctorSchedule::where('id', $scheduleId)->first();
        if (!$getSchedule) {
            return false;
        }

        $getService = Service::where('id', $getSchedule->service_id)->first();
        if (!$getService) {
            return false;
        }

        $getUser = Users::where('id', $getTransaction->user_id)->first();
        $getDoctorData = Doctor::where('id', $getTransactionDetails->doctor_id)->first();
        $getDoctor = Users::where('id', $getDoctorData->user_id)->first();

        $getTotal = AppointmentDoctor::where('klinik_id', $getTransaction->klinik_id)
                    ->where('doctor_id', $getDoctorData->id)
                    ->where('service_id', 1)
                    ->whereYear('created_at', '=', date('Y'))
                    ->whereMonth('created_at', '=', date('m'))
                    ->whereDate('created_at', '=', date('d'))
                    ->count();

        $getTotalCode = str_pad(($getTotal + 1), 2, '0', STR_PAD_LEFT);

        //dd($getTotalCode);
        $newCode =  date('d').$getDoctorData->id.$getTotalCode;


        if ($flag) {
            AppointmentDoctor::create([
                'transaction_id' => $getTransaction->id,
                'klinik_id' => $getTransaction->klinik_id,
                'code' => $newCode,
                'schedule_id' => $getSchedule->id,
                'service_id' => $getSchedule->service_id,
                'doctor_id' => $getSchedule->doctor_id,
                'user_id' => $getTransaction->user_id,
                'type_appointment' => $getService ? $getService->name : '',
                'patient_name' => $getUser ? $getUser->fullname : '',
                'patient_email' => $getUser ? $getUser->email : '',
                'doctor_name' => $getDoctor ? $getDoctor->fullname : '',
                'date' => $getSchedule->date_available,
                'time_start' => $getSchedule->time_start,
                'time_end' => $getSchedule->time_end,
                'status' => 1
            ]);
        }
        else {
            DoctorSchedule::where('id', $scheduleId)->update([
                'book' => 80
            ]);
        }

        return true;

    }

    public function setupAppointmentLab($getTransaction, $getTransactionDetails, $scheduleId, $flag = true)
    {
        $getSchedule = LabSchedule::where('id', $scheduleId)->first();
        if (!$getSchedule) {
            return false;
        }

        $getService = Service::where('id', $getSchedule->service_id)->first();
        if (!$getService) {
            return false;
        }

        $getUser = Users::where('id', $getTransaction->user_id)->first();
        $getTotal = AppointmentLab::where('klinik_id', $getTransaction->klinik_id)
            ->where('service_id', $getService->id)
            ->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))
            ->whereDate('created_at', '=', date('d'))
            ->count();

        $getTotalCode = str_pad(($getTotal + 1), 3, '0', STR_PAD_LEFT);

        //dd($getTotalCode);
        $newCode = date('ym').$getService->id.$getTotalCode;


        if ($flag) {
            DB::beginTransaction();

            $getAppointmentLab = AppointmentLab::create([
                'transaction_id' => $getTransaction->id,
                'code' => $newCode,
                'klinik_id' => $getTransaction->klinik_id,
                'schedule_id' => $getSchedule->id,
                'service_id' => $getSchedule->service_id,
                'user_id' => $getTransaction->user_id,
                'patient_name' => $getUser ? $getUser->fullname : '',
                'patient_email' => $getUser ? $getUser->email : '',
                'type_appointment' => $getService->name,
                'date' => $getSchedule->date_available,
                'time_start' => $getSchedule->time_start,
                'time_end' => $getSchedule->time_end,
                'status' => 1
            ]);
            foreach ($getTransactionDetails as $getTransactionDetail) {
                AppointmentLabDetails::create([
                    'appointment_lab_id' => $getAppointmentLab->id,
                    'lab_id' => $getTransactionDetail->lab_id,
                    'lab_name' => $getTransactionDetail->lab_name,
                    'lab_price' => $getTransactionDetail->lab_price,
                    'status' => 1
                ]);
            }

            DB::commit();

        }

        return true;

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
        Transaction::where('status', 2)->where('created_at', '<', $timeFinish)->update([
            'status' => 99
        ]);
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
        $file = env('OSS_URL') . '/' . 'synapsaapps/lab-schedule/example_import/rqTH6V46UlU725sIwxslokh7G5XdUiDCXW7YIYA2.xlsx';
        $fileName = create_slugs('Example Import Lab Schedule');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
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
        $file = env('OSS_URL') . '/' . 'synapsaapps/doctor-schedule/example_import/kPG7tGSa4TXQyadj1JBGgLyvIPNbDYJ2w3LQMq1F.xlsx';
        $fileName = create_slugs('Example Import Doctor Schedule');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        readfile($file);
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
