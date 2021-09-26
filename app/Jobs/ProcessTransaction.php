<?php

namespace App\Jobs;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobId;
    protected $getJob;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
        $this->getJob = SetJob::where('id', $this->jobId)->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $getJob = SetJob::where('id', $this->jobId)->first();
        if ($getJob && $getJob->status == 1) {

            $getParams = json_decode($getJob->params, TRUE);
            $getTypeService = isset($getParams['type_service']) ? $getParams['type_service'] : '';
            $getServiceId = isset($getParams['service_id']) ? intval($getParams['service_id']) : 0;
            $getUserId = isset($getParams['user_id']) ? intval($getParams['user_id']) : 0;
            $getPaymentId = isset($getParams['payment_id']) ? intval($getParams['payment_id']) : 0;
            $getType = check_list_type_transaction($getTypeService, $getServiceId);
            if ($getType == 1) {
                $this->transactionProduct();
            }
            else if (in_array($getType, [2,3,4])) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
                $this->transactionDoctor($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo);
            }
            else if (in_array($getType, [5,6,7])) {
                $this->transactionLab();
            }
        }
    }

    private function transactionProduct()
    {

    }

    private function transactionDoctor($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo)
    {
        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
        $getDoctorSchedule = DoctorSchedule::where('id', $getScheduleId)->first();
        $getPayment = Payment::where('id', $getPaymentId)->first();
        $getUser = Users::where('id', $getUserId)->first();

        $subTotal = $getDoctorInfo['price'];
        $total = $subTotal;

        $getTotal = Transaction::where('klinik_id', $getUser->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        $extraInfo = [
            'service_id' => $getDoctorSchedule->service_id,
            'phone' => $getUser->phone ?? ''
        ];

        foreach (['address_name', 'address', 'city_id', 'city_name', 'district_id', 'district_name',
                     'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
            $extraInfo[$key] = $getUsersAddress->$key;
        }

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'code' => $newCode,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'type' => $getType,
            'subtotal' => $subTotal,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'status' => 1
        ]);

        TransactionDetails::create([
            'transaction_id' => $getTransaction->id,
            'schedule_id' => $getScheduleId,
            'doctor_id' => $getDoctorInfo['id'],
            'doctor_name' => $getDoctorInfo['doctor_name'],
            'doctor_price' => $subTotal
        ]);

        DoctorSchedule::where('id', $getScheduleId)->update([
            'book' => 99
        ]);

        DB::commit();

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $getTransaction, [
            'name' => $getUser->name
        ]);

        $getTransaction->payment_info = json_encode($getPaymentInfo);
        $getTransaction->status = 2;
        $getTransaction->save();

        $this->getJob->status = 2;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();

    }

    private function transactionLab()
    {

    }

}
