<?php

namespace App\Jobs;

use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
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
            $getType = isset($getParams['type']) ? intval($getParams['type']) : 0;
            $getPaymentReferId = $getParams['payment_refer_id'] ?? '';
            $getPaymentInfo = $getParams['payment_info'] ?? '';
            $getNewCode = $getParams['code'] ?? '';
            if ($getType == 1) {
                $this->transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo);
            }
            else if (in_array($getType, [2,3,4])) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
                $this->transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo);
            }
            else if (in_array($getType, [5,6,7])) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getLabInfo = isset($getParams['lab_info']) ? $getParams['lab_info'] : [];
                $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo);
            }
            else if (in_array($getType, [8,9,10])) {
                $this->transactionNurse();
            }
        }
    }

    private function transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo)
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

        $newCode = date('Ym').$getNewCode;

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
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'shipping_id' => $getShippingId,
            'shipping_name' => $getShipping->name,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getDetailsInformation['receiver'] ?? '',
            'receiver_address' => $getDetailsInformation['address'] ?? '',
            'receiver_phone' => $getDetailsInformation['phone'] ?? '',
            'shipping_address_name' => $getUsersAddress->address_name ?? '',
            'shipping_address' => $getUsersAddress->address ?? '',
            'shipping_city_id' => $getUsersAddress->city_id ?? 0,
            'shipping_city_name' => $getUsersAddress->city_name ?? '',
            'shipping_district_id' => $getUsersAddress->district_id ?? 0,
            'shipping_district_name' => $getUsersAddress->district_name ?? '',
            'shipping_subdistrict_id' => $getUsersAddress->sub_district_id ?? 0,
            'shipping_subdistrict_name' => $getUsersAddress->sub_district_name ?? '',
            'shipping_zipcode' => $getUsersAddress->zip_code ?? '',
            'payment_info' => $getPaymentInfo,
            'category_service_id' => 0,
            'category_service_name' => '',
            'type_service' => 1,
            'type_service_name' => $getTypeService,
            'total_qty' => $totalQty,
            'subtotal' => $subTotal,
            'shipping_price' => $shippingPrice,
            'total' => $total,
            'status' => 2
        ]);

        $getTransaction->getTransactionDetails()->saveMany($transactionDetails);

        UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->delete();

        DB::commit();

        $this->getJob->status = 80;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();

    }

    private function transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo)
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

        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
        $getPayment = Payment::where('id', $getPaymentId)->first();
        $getUser = Users::where('id', $getUserId)->first();
        $getService = Service::where('id', $getDoctorSchedule->service_id)->first();

        $subTotal = $getDoctorInfo['price'];
        $total = $subTotal;

        $newCode = date('Ym').$getNewCode;

        $extraInfo = [
            'service_id' => $getDoctorSchedule->service_id,
            'phone' => $getUser->phone ?? ''
        ];

        if ($getUsersAddress) {
            foreach (['address_name', 'address', 'city_id', 'city_name', 'district_id', 'district_name',
                         'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
                $extraInfo[$key] = isset($getUsersAddress->$key) ? $getUsersAddress->$key : '';
            }
        }

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'code' => $newCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'category_service_id' => $getDoctorSchedule->service_id,
            'category_service_name' => $getService ? $getService->name : '',
            'type_service' => 2,
            'type_service_name' => $getTypeService,
            'subtotal' => $subTotal,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'payment_info' => $getPaymentInfo,
            'status' => 2
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

        $this->getJob->status = 80;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();

    }

    private function transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo)
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

        $getData = $getLabInfo;
        $total = 0;
        foreach ($getData as $list) {
            $total += $list['price'];
        }

        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
        $getPayment = Payment::where('id', $getPaymentId)->first();
        $getUser = Users::where('id', $getUserId)->first();
        $getService = Service::where('id', $getLabSchedule->service_id)->first();

        $newCode = date('Ym').$getNewCode;

        $extraInfo = [
            'service_id' => $getLabSchedule->service_id,
            'phone' => $getUser->phone ?? ''
        ];

        if ($getUsersAddress) {
            foreach (['address_name', 'address', 'city_id', 'city_name', 'district_id', 'district_name',
                         'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
                $extraInfo[$key] = isset($getUsersAddress->$key) ? $getUsersAddress->$key : '';
            }
        }

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $newCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'payment_info' => $getPaymentInfo,
            'category_service_id' => $getLabSchedule->service_id,
            'category_service_name' => $getService ? $getService->name : '',
            'type_service' => 3,
            'type_service_name' => $getTypeService,
            'subtotal' => $total,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'status' => 2
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

        DB::commit();

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
