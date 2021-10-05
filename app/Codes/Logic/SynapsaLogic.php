<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\DB;

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

            if (isset($getData->result->ewallet_type) && in_array($getData->result->ewallet_type, ['OVO', 'DANA']) || $getData->result->status == 'REQUEST_RECEIVED') {
                $success = 1;
                $getInfo = $getData->result;

                $getTypeService = $additional['job']['type_service'];
                $getServiceId = $additional['job']['service_id'];
                $getType = check_list_type_transaction($getTypeService, $getServiceId);

                $getJobData = $additional['job'];
                $getJobData['payment_refer_id'] = isset($getData->result->external_id) ? $getData->result->external_id : '';
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
        else if ($payment->service == 'xendit' && in_array($payment->type_payment, ['qr_qris'])) {
            $getData = (object)$this->sendPayment($payment, $additional);

            $success = 1;
            $getInfo = $getData->result;

            $getTypeService = $additional['job']['type_service'];
            $getServiceId = $additional['job']['service_id'];
            $getType = check_list_type_transaction($getTypeService, $getServiceId);

            $getJobData = $additional['job'];
            $getJobData['payment_refer_id'] = isset($getData->result->external_id) ? $getData->result->external_id : '';
            $getJobData['type'] = $getType;

            $job = SetJob::create([
                'status' => 1,
                'params' => json_encode($getJobData)
            ]);

            dispatch((new ProcessTransaction($job->id))->onQueue('high'));

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
                case 'qr_qris': $result = $xendit->createQrQris($getCode, $getTotal, $getPhone);
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

        if ($flag) {
            AppointmentDoctor::create([
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

        if ($flag) {
            DB::beginTransaction();

            $getAppointmentLab = AppointmentLab::create([
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

    public function setupAppointmentNurse($scheduleId, $transactionId, $flag = true)
    {
        $getTransaction = Transaction::where('id', $transactionId)->first();
        if (!$getTransaction) {
            return false;
        }

        return true;

    }

}
