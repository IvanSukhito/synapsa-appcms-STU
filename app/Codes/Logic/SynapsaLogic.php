<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Jobs\ProcessTransaction;

class SynapsaLogic
{
    public function __construct()
    {
    }

    public function createPayment($payment, $additional)
    {
        $transactionId = 0;
        $success = 0;
        $message = 'OK';
        $getInfo = [];
        if ($payment->service == 'xendit' && substr($payment->type_payment, 0, 3) == 'va_') {
            $getData = (object)$this->sendPayment($payment, $additional);
            if ($getData->result->status == 'PENDING') {
                $success = 1;
                $getInfo = json_decode($payment->settings, true);
                $getInfo['price'] = $additional['total'];
                $getInfo['price_nice'] = number_format($additional['total'], 0, ',', '.');
                $getInfo['va_user'] = $getData->result->account_number;

                $getTypeService = $additional['job']['type_service'];
                $getServiceId = $additional['job']['service_id'];
                $getType = check_list_type_transaction($getTypeService, $getServiceId);

                $getJobData = $additional['job'];
                $getJobData['payment_refer_id'] = $getData->result->external_id;
                $getJobData['type'] = $getType;
                $getJobData['payment_info'] = json_encode($getInfo);

                $job = SetJob::create([
                    'status' => 1,
                    'params' => json_encode($getJobData)
                ]);

                dispatch((new ProcessTransaction($job->id))->onQueue('high'));

            }
            else {
                $message = $getData->result->message;
            }
        }
        else if ($payment->service == 'xendit' && in_array($payment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
            $getData = (object)$this->sendPayment($payment, $additional);
            if (in_array($getData->result->ewallet_type, ['OVO', 'DANA']) || $getData->result->status == 'REQUEST_RECEIVED') {
                $success = 1;
                $getInfo = $getData->result;

                $getTypeService = $additional['job']['type_service'];
                $getServiceId = $additional['job']['service_id'];
                $getType = check_list_type_transaction($getTypeService, $getServiceId);

                $getJobData = $additional['job'];
                $getJobData['payment_refer_id'] = $getData->result->external_id;
                $getJobData['type'] = $getType;

                $job = SetJob::create([
                    'status' => 1,
                    'params' => json_encode($getJobData)
                ]);

                dispatch((new ProcessTransaction($job->id))->onQueue('high'));

            }
            else {
                $message = $getData->result->message;
            }
        }
        else {
            $message = 'Payment Error';
        }

        return [
            'success' => $success,
            'info' => $getInfo,
            'transaction_id' => $transactionId,
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
            switch ($payment->type_payment) {
                case 'va_bca':
                case 'va_bri':
                case 'va_bni':
                case 'va_bjb':
                case 'va_cimb':
                case 'va_mandiri':
                case 'va_permata':
                    $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                    $result = $xendit->createVA($getCode, $getTypePayment, $getTotal, $getName);
                    break;
                case 'ew_ovo': $result = $xendit->createEWalletOVO($getCode, $getTotal, $getPhone);
                    break;
                case 'ew_dana': $result = $xendit->createEWalletDANA($getCode, $getTotal, $getPhone);
                    break;
                case 'ew_linkaja': $result = $xendit->createEWalletLINKAJA($getCode, $getTotal, $getPhone);
                    break;
            }

            if ($result) {
                $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                $getLogId = LogServiceTransaction::create([
                    'transaction_refer_id' => isset($result->external_id) ? $result->external_id : '',
                    'service' => 'xendit',
                    'type_payment' => $getTypePayment,
                    'type_transaction' => 'create',
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

    public function setupAppointmentDoctor($scheduleId, $transactionId, $flag = true)
    {
        $getTransaction = Transaction::where('id', $transactionId)->first();
        if (!$getTransaction) {
            return false;
        }
        $getSchedule = DoctorSchedule::where('id', $scheduleId)->first();
        if ($getSchedule) {
            return false;
        }

        $getService = Service::where('id', $getSchedule->service_id);
        if (!$getService) {
            return false;
        }

        if ($flag) {
            AppointmentDoctor::create([
                'service_id' => $getSchedule->service_id,
                'doctor_id' => $getSchedule->doctor_id,
                'user_id' => $getTransaction->user_id,
                'type_appointment' => $getService->name,
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

}
