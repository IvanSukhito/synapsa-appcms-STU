<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\AccessLogin;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\ForgetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    }

    public function postTransactionResult()
    {
        Log::info("POST");
        Log::info(json_encode($this->request->all()));
        $getExternalId = $this->request->get('external_id');
        $getAmount = $this->request->get('amount');
        if ($getExternalId) {
            if (substr($getExternalId, 0, 7) == 'va-fix-' && $getAmount) {
                $getTransaction = Transaction::where('payment_refer_id', $getExternalId)->first();
                if ($getTransaction) {
                    if ($getAmount >= $getTransaction->total) {

                        $this->updateTransaction($getTransaction);

                    }
                }
            }
        }

    }

    public function approveTransactionVa()
    {
        $getCode = $this->request->get('code');

        $getTransaction = Transaction::where('payment_refer_id', $getCode)->first();
        if ($getTransaction) {

            $this->updateTransaction($getTransaction);

        }

    }

    public function approveTransaction()
    {
        $getCode = $this->request->get('id');

        $getTransaction = Transaction::where('id', $getCode)->first();
        if ($getTransaction) {

            $this->updateTransaction($getTransaction);

        }

    }

    public function updateTransaction($getTransaction)
    {
        $getType = $getTransaction->type;
        $getTransaction->status = 80;
        $getTransaction->save();

        if (in_array($getType, [2, 3, 4])) {
            $transactionId = $getTransaction->id;
            $getDetail = TransactionDetails::where('transaction_id', $transactionId)->first();
            if ($getDetail) {
                $logic = new SynapsaLogic();
                $logic->setupAppointmentDoctor($getTransaction, $getDetail, $getDetail->schedule_id);
            }
        } else if (in_array($getType, [5, 6, 7])) {
            $transactionId = $getTransaction->id;
            $getDetail = TransactionDetails::where('transaction_id', $transactionId)->first();
            if ($getDetail) {
                $logic = new SynapsaLogic();
                $logic->setupAppointmentLab($getDetail->schedule_id, $transactionId);
            }
        } else if (in_array($getType, [8, 9, 10])) {
            $transactionId = $getTransaction->id;
            $getDetail = TransactionDetails::where('transaction_id', $transactionId)->first();
            if ($getDetail) {
                $logic = new SynapsaLogic();
                $logic->setupAppointmentNurse($getDetail->schedule_id, $transactionId);
            }
        }

    }

}
