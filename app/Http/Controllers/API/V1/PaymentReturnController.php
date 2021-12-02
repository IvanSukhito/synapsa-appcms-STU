<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\LabLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentReturnController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getTransactionResult()
    {
        Log::info("GET");
        Log::info(json_encode($this->request->all()));
        return 'must go to apps';
    }

    public function postTransactionResult()
    {
        Log::info("POST");
        Log::info(json_encode($this->request->all()));
        $getExternalId = $this->request->get('external_id');
        $getAmount = $this->request->get('amount');
        $getStatus = $this->request->get('status');
        if ($getExternalId) {
            if (substr($getExternalId, 0, 7) == 'va-fix-' && $getAmount) {
                $getTransaction = Transaction::where('payment_refer_id', $getExternalId)->first();
                if ($getTransaction) {
                    if ($getAmount >= $getTransaction->total) {
                        return $this->updateTransaction($getTransaction);
                    }
                }
            } else if (substr($getExternalId, 0, 3) == 'ew-' && $getAmount && $getStatus && $getStatus == 'COMPLETED') {
                $getTransaction = Transaction::where('payment_refer_id', $getExternalId)->first();
                if ($getTransaction) {
                    if ($getAmount >= $getTransaction->total) {
                        return $this->updateTransaction($getTransaction);
                    }
                }
            }
        }

        return 0;

    }

    public function approveTransactionVa()
    {
        $getCode = $this->request->get('code');

        $getTransaction = Transaction::where('payment_refer_id', $getCode)->first();
        if ($getTransaction) {
            return $this->updateTransaction($getTransaction);
        }
        return 0;
    }

    public function approveTransaction()
    {
        $getCode = $this->request->get('id');

        $getTransaction = Transaction::where('id', $getCode)->first();
        if ($getTransaction) {
            return $this->updateTransaction($getTransaction);
        }
        return 0;
    }

    public function updateTransaction($getTransaction)
    {
        if ($getTransaction->status != 2) {
            return 0;
        }
        $getResult = 0;
        $userId = $getTransaction->user_id;

        $getType = $getTransaction->type_service;
        $getTransaction->status = 81;
        $getTransaction->save();

        if ($getType == 2) {

            $doctorLogic = new DoctorLogic();
            $getResult = $doctorLogic->appointmentSuccess([$getTransaction->id]);

        }
        else if ($getType == 3) {
            $transactionId = $getTransaction->id;
            $getDetails = TransactionDetails::where('transaction_id', $transactionId)->get();
            $extraInfo = [];
            $scheduleId = 0;
            foreach ($getDetails as $getDetail) {
                $extraInfo = json_decode($getDetail->extra_info, true);
                $scheduleId = $getDetail->schedule_id;
            }
            if ($getDetails->count() > 0) {
                $getDate = $extraInfo['date'] ?? '';
                $getServiceName = $extraInfo['service_name'] ?? '';

                $getUser = Users::where('id', $getTransaction->user_id)->first();

                $labLogic = new LabLogic();
                $getResult = $labLogic->appointmentCreate($scheduleId, $getDate, $getUser,
                    $getServiceName, $getTransaction, $getTransaction->code, $extraInfo, $getDetails);
            }
        }
        else if ($getType == 4) {
            $transactionId = $getTransaction->id;
            $getDetail = TransactionDetails::where('transaction_id', $transactionId)->first();
            if ($getDetail) {
                $logic = new SynapsaLogic();
                $logic->setupAppointmentNurse($getTransaction, $getDetail->schedule_id, $transactionId);
            }
        }

        if ($getResult == 1) {
            $title = 'Transaksi kamu berhasil';
            $message = 'Selamat Transaksi kamu berhasil di terima, pesanan kamu akan segera di proses';
            dispatch((new ProcessNotification([$userId], $title, $message))->onQueue('high'));
        }

        return $getResult;

    }

}
