<?php

namespace App\Jobs;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\UsersCartDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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
        if ($this->getJob && $this->getJob->status == 1) {

            $getParams = json_decode($this->getJob->params, TRUE);
            $getTypeService = isset($getParams['type_service']) ? $getParams['type_service'] : '';
            $getServiceId = isset($getParams['service_id']) ? intval($getParams['service_id']) : 0;
            $getUserId = isset($getParams['user_id']) ? intval($getParams['user_id']) : 0;
            $getPaymentId = isset($getParams['payment_id']) ? intval($getParams['payment_id']) : 0;
            $getType = check_list_type_transaction($getTypeService, $getServiceId);
            if ($getType == 1) {
                $this->transactionProduct($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId);
            }
            else if (in_array($getType, [2,3,4])) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
                $this->transactionDoctor($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo);
            }
            else if (in_array($getType, [5,6,7])) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getLabInfo = isset($getParams['lab_info']) ? $getParams['lab_info'] : [];
                $this->transactionLab($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo);
            }
            else if (in_array($getType, [8,9,10])) {
                $this->transactionNurse();
            }
        }
    }

    private function transactionProduct($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
        $getPayment = Payment::where('id', $getPaymentId)->first();
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $getUserId,
        ]);
        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
        $getShippingId = $getDetailsShipping['shipping_id'];
        $getShipping = Shipping::where('id', $getShippingId)->first();
        if (!$getShipping) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Pengiriman Tidak Ditemukan'
            ]);
            $this->getJob->save();
            return;
        }
        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->count();
        if ($getUsersCartDetail <= 0) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Tidak ada Produk yang di pilih'
            ]);
            $this->getJob->save();
            return;
        }

        $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.id AS product_id, product.name AS product_name,
            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();

        $getTotal = Transaction::where('klinik_id', $getUser->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        $totalQty = 0;
        $subTotal = 0;
        $shippingPrice = 15000;
        $transactionDetails = [];
        foreach ($getUsersCartDetails as $list) {
            $totalQty += $list->qty;
            $subTotal += ($list->qty * $list->price);
            $transactionDetails[] = new TransactionDetails([
                'product_id' => $list->product_id,
                'product_name' => $list->product_name,
                'product_qty' => $list->qty,
                'product_price' => $list->price
            ]);
        }
        $total = $subTotal + $shippingPrice;

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $newCode,
            'shipping_id' => $getShippingId,
            'shipping_name' => $getShipping->name,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getDetailsInformation['receiver'] ?? '',
            'receiver_address' => $getDetailsInformation['address'] ?? '',
            'receiver_phone' => $getDetailsInformation['phone'] ?? '',
            'shipping_address_name' => $getUsersAddress->address_name ?? '',
            'shipping_address' => $getUsersAddress->address ?? '',
            'shipping_city_id' => $getUsersAddress->city_id ?? '',
            'shipping_city_name' => $getUsersAddress->city_name ?? '',
            'shipping_district_id' => $getUsersAddress->district_id ?? '',
            'shipping_district_name' => $getUsersAddress->district_name ?? '',
            'shipping_subdistrict_id' => $getUsersAddress->sub_district_id ?? '',
            'shipping_subdistrict_name' => $getUsersAddress->sub_district_name ?? '',
            'shipping_zipcode' => $getUsersAddress->zip_code ?? '',
            'type' => 1,
            'total_qty' => $totalQty,
            'subtotal' => $subTotal,
            'shipping_price' => $shippingPrice,
            'total' => $total,
            'status' => 1
        ]);

        $getTransaction->getTransactionDetails()->saveMany($transactionDetails);

        UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->delete();

        DB::commit();

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $getTransaction, [
            'name' => $getUser->fullname
        ], $this->getJob->id);

        $getTransaction->payment_info = json_encode($getPaymentInfo);
        $getTransaction->status = 2;
        $getTransaction->save();

        $this->getJob->status = 80;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();

    }

    private function transactionDoctor($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', $getScheduleId)->first();
        if (!$getDoctorSchedule) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Tidak Ditemukan'
            ]);
            $this->getJob->save();
            return;

        }
        else if ($getDoctorSchedule->book != 80) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Sudah Dipesan'
            ]);
            $this->getJob->save();
            return;
        }
        else if (date('Y-m-d', strtotime($getDoctorSchedule->date_available)) < date('Y-m-d')) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Sudah Lewat Waktunya'
            ]);
            $this->getJob->save();
            return;
        }

        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
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
            'name' => $getUser->fullname
        ], $this->getJob->id);

        if ($getPaymentInfo && isset($getPaymentInfo->status) && $getPaymentInfo->status == "GAGAL") {
            $getTransaction->status = 90;
        }
        else {
            $getTransaction->status = 2;
        }

        $getTransaction->payment_info = json_encode($getPaymentInfo);
        $getTransaction->save();

        if ($getPaymentInfo && isset($getPaymentInfo->status) && $getPaymentInfo->status == "GAGAL") {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => $getPaymentInfo->message
            ]);
            $this->getJob->save();
        }
        else {
            $this->getJob->status = 80;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'transaction_id' => $getTransaction->id,
                'message' => 'ok'
            ]);
            $this->getJob->save();
        }

    }

    private function transactionLab($getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo)
    {
        $getLabSchedule = LabSchedule::where('id', $getScheduleId)->first();
        if (!$getLabSchedule) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Tidak Ditemukan'
            ]);
            $this->getJob->save();
            return;

        }
        else if ($getLabSchedule->book != 80) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Sudah Dipesan'
            ]);
            $this->getJob->save();
            return;
        }
        else if (date('Y-m-d', strtotime($getLabSchedule->date_available)) < date('Y-m-d')) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Jadwal Sudah Lewat Waktunya'
            ]);
            $this->getJob->save();
            return;
        }

        $getData = $getLabInfo;
        $total = 0;
        foreach ($getData as $list) {
            $total += $list['price'];
        }

        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
        $getPayment = Payment::where('id', $getPaymentId)->first();
        $getUser = Users::where('id', $getUserId)->first();

        $getTotal = Transaction::where('klinik_id', $getUser->klinik_id)->whereYear('created_at', '=', date('Y'))
        ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        $extraInfo = [
            'service_id' => $getLabSchedule->service_id,
            'address_name' => $getUsersAddress->address_name ?? '',
            'address' => $getUsersAddress->address ?? '',
            'city_id' => $getUsersAddress->city_id ?? '',
            'district_id' => $getUsersAddress->district_id ?? '',
            'sub_district_id' => $getUsersAddress->sub_district_id ?? '',
            'zip_code' => $getUsersAddress->zip_code ?? '',
            'phone' => $getUser->phone ?? ''
        ];

        foreach (['address_name', 'address', 'city_id', 'city_name', 'district_id', 'district_name',
        'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
        $extraInfo[$key] = $getUsersAddress->$key;}

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $newCode,
            'service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'type' => $getType,
            'subtotal' => $total,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'status' => 1
        ]);

        $listTransactionDetails = [];
        foreach ($getData as $list) {
            $getTransactionDetails = TransactionDetails::create([
                'transaction_id' => $getTransaction->id,
                'schedule_id' => $getScheduleId,
                'lab_id' => $list['lab_id'],
                'lab_name' => $list['name'],
                'lab_price' => $list['price']
            ]);
            $listTransactionDetails[] = $getTransactionDetails;
        }

        LabCart::where('user_id', $getUser->id)->where('choose', '=', 1)->delete();

        LabSchedule::where('id', $getScheduleId)->update([
            'book' => 99
        ]);

        DB::commit();

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $getTransaction, [
            'name' => $getUser->fullname
        ], $this->getJob->id);

        $getTransaction->payment_info = json_encode($getPaymentInfo);
        $getTransaction->status = 2;
        $getTransaction->save();

        $this->getJob->status = 80;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();
    }

    private function transactionNurse()
    {

    }

}
