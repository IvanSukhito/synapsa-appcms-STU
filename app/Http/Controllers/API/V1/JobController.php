<?php

namespace App\Http\Controllers\API\V1;

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
        if ($getJobData && $getJobData->status == 2) {
            $getResponse = json_decode($getJobData->response, true);
            $getTransactionId = isset($getResponse['transaction_id']) ? intval($getResponse['transaction_id']) : 0;
            $getTransaction = Transaction::where('id', $getTransactionId)->first();
            if ($getTransaction) {
                return response()->json([
                    'success' => 1,
                    'data' => $getTransaction,
                    'message' => ['Berhasil'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ]);
            }
        }

        return response()->json([
            'success' => 0,
            'message' => ['Job Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

    }

}
