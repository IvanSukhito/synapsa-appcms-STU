<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function jobDetail($jobId)
    {
        $user = $this->request->attributes->get('_user');

        $getJobData = SetJob::where('id', $jobId)->first();
        if ($getJobData) {
            if ($getJobData->status == 80) {
                $getResponse = json_decode($getJobData->response, true);
                $getTransactionId = isset($getResponse['transaction_id']) ? intval($getResponse['transaction_id']) : 0;
                $getTransaction = Transaction::where('id', $getTransactionId)->first();
                if ($getTransaction) {

                    $getLogServiceTransaction = LogServiceTransaction::where('transaction_id', $getTransactionId)->orderBy('id', 'DESC')->first();
                    if ($getLogServiceTransaction) {
                        if ($getLogServiceTransaction->type_transaction == 'complete') {

                            return response()->json([
                                'success' => 1,
                                'data' => [
                                    'payment' => 1
                                ],
                                'message' => ['Pembayaran Berhasil'],
                                'token' => $this->request->attributes->get('_refresh_token'),
                            ]);

                        }
                        else {
                            $getLogServiceTransaction = LogServiceTransaction::where('transaction_id', $getTransactionId)->where('type_transaction', 'create')->first();
                            $getResultResponse = json_decode($getLogServiceTransaction->results, true);
                            $getPayment = Payment::where('id', $getTransaction->payment_id)->first();
                            $getSetting = json_decode($getPayment->settings, true);
                            if (in_array($getPayment->type_payment, ['va_bca', 'va_bri', 'va_bni', 'va_bjb', 'va_cimb', 'va_mandiri', 'va_permata'])) {
                                $getInfo = $getSetting;
                                $getInfo['price'] = $getTransaction->total;
                                $getInfo['price_nice'] = $getTransaction->total_nice;
                                $getInfo['va_user'] = isset($getResultResponse['account_number']) ? $getResultResponse['account_number'] : '';
                            }
                            else {
                                $getInfo = [
                                ];
                            }

                            return response()->json([
                                'success' => 1,
                                'data' => [
                                    'payment' => 0,
                                    'info' => $getInfo
                                ],
                                'message' => ['Berhasil'],
                                'token' => $this->request->attributes->get('_refresh_token'),
                            ]);

                        }
                    }

                }
            }
            else {
                $getResponse = json_decode($getJobData->response, true);
                return response()->json([
                    'success' => 0,
                    'message' => [$getResponse['message']],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
        }

        return response()->json([
            'success' => 0,
            'message' => ['Job Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

    }

}
