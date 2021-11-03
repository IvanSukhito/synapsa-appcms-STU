<?php

namespace App\Jobs;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\BookNurse;
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
use Illuminate\Database\Eloquent\Model;
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
            $getDetailsInfo =  $getParams['detail_info'] ?? '';
            $getShippingId = isset($getParams['shipping_id']) ? intval($getParams['shipping_id']) : 0;
            $additional = isset($getParams['additional']) ? $getParams['additional'] : '';
            if ($getType == 1) {
                $this->transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo, $additional);
            }
            else if ($getType == 2) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
                $this->transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo, $additional);
            }
            else if ($getType == 3) {
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getLabInfo = isset($getParams['lab_info']) ? $getParams['lab_info'] : [];
                $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo, $additional);
            }
            else if ($getType == 4){
                $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
                $getNurseInfo = isset($getParams['nurse_info']) ? $getParams['nurse_info'] : [];
                $this->transactionNurse($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional);
            }
            else if ($getType == 5) {
                $getAppointmentDoctorId = isset($getParams['appointment_doctor_id']) ? intval($getParams['appointment_doctor_id']) : 0;
                $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional);
            }
        }
    }

    private function transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo, $additional)
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

        //dd($getUsersCartDetails);

        DB::beginTransaction();

        $totalQty = 0;
        $subTotal = 0;
        $shippingPrice = 15000;
        $transactionDetails = [];
        $productQty = [];
        $getProductIds = [];
        foreach ($getUsersCartDetails as $list) {
           // dd($list->qty);
            $totalQty += $list->qty;

            $subTotal += ($list->qty * $list->price);

            $productQty[] = $list->qty;
            $getProductIds[] = $list->product_id;
            $transactionDetails[] = new TransactionDetails([
                'product_id' => $list->product_id,
                'product_name' => $list->product_name,
                'product_qty' => $list->qty,
                'product_price' => $list->price
            ]);
        }


        $getProducts = Product::whereIn('id', $getProductIds)->get();

        foreach ($getProducts as $key => $list){

            $qty = $productQty[$key];

            if($list->stock_flag == 2) {

                $list->stock = $list->stock - $qty;

                $list->save();
            }
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
            'shipping_province_id' => $getUsersAddress->province_id ?? 0,
            'shipping_province_name' => $getUsersAddress->province_name ?? '',
            'shipping_city_id' => $getUsersAddress->city_id ?? 0,
            'shipping_city_name' => $getUsersAddress->city_name ?? '',
            'shipping_district_id' => $getUsersAddress->district_id ?? 0,
            'shipping_district_name' => $getUsersAddress->district_name ?? '',
            'shipping_subdistrict_id' => $getUsersAddress->sub_district_id ?? 0,
            'shipping_subdistrict_name' => $getUsersAddress->sub_district_name ?? '',
            'shipping_zipcode' => $getUsersAddress->zip_code ?? '',
            'send_info' => $additional,
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

    private function transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();

        $getPayment = Payment::where('id', $getPaymentId)->first();

        $getReceiver = json_decode($getDetailsInfo, true);

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $getUserId)->where('id', $getAppointmentDoctorId)->first();
        if (!$data) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Janji Temu Dokter Tidak Ditemukan'
            ]);
            $this->getJob->save();
            return;
        }

        $getShipping = Shipping::where('id', $getShippingId)->first();

        $getShippingPrice = 15000;

        $total = 0;
        $getUsersCartDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty as product_qty, product_qty_checkout, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $getAppointmentDoctorId)->where('choose', 1)
            ->get();

        foreach ($getUsersCartDetails as $list) {
            $total += $list->price;
        }

        $total += $getShippingPrice;

        $newCode = date('Ym').$getNewCode;

        DB::beginTransaction();

        $totalQty = 0;
        $subTotal = 0;
        $shippingPrice = 15000;
        $transactionDetails = [];
        $productQty = [];
        $getProductIds = [];
        foreach ($getUsersCartDetails as $list) {

            $totalQty += $list->product_qty_checkout;

            $subTotal += ($list->product_qty_checkout * $list->price);

            $productQty[] = $list->product_qty_checkout;

            $getProductIds[] = $list->product_id;

            $transactionDetails[] = new TransactionDetails([
                'product_id' => $list->product_id,
                'product_name' => $list->product_name,
                'product_qty' => $list->product_qty_checkout,
                'product_price' => $list->price
            ]);
        }


        $getProducts = Product::whereIn('id', $getProductIds)->get();

        foreach ($getProducts as $key => $list){

            $qty = $productQty[$key];

            if($list->stock_flag == 2) {

                $list->stock = $list->stock - $qty;

                $list->save();
            }

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
            'receiver_name' => $getReceiver['receiver_name'] ?? '',
            'receiver_address' => $getReceiver['receiver_address'] ?? '',
            'receiver_phone' => $getReceiver['receiver_phone'] ?? '',
            'shipping_address_name' => $getUsersAddress->address_name ?? '',
            'shipping_address' => $getUsersAddress->address ?? '',
            'shipping_province_id' => $getUsersAddress->province_id ?? 0,
            'shipping_province_name' => $getUsersAddress->province_name ?? '',
            'shipping_city_id' => $getUsersAddress->city_id ?? 0,
            'shipping_city_name' => $getUsersAddress->city_name ?? '',
            'shipping_district_id' => $getUsersAddress->district_id ?? 0,
            'shipping_district_name' => $getUsersAddress->district_name ?? '',
            'shipping_subdistrict_id' => $getUsersAddress->sub_district_id ?? 0,
            'shipping_subdistrict_name' => $getUsersAddress->sub_district_name ?? '',
            'shipping_zipcode' => $getUsersAddress->zip_code ?? '',
            'send_info' => $additional,
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

        AppointmentDoctorProduct::where('appointment_doctor_id', '=', $getAppointmentDoctorId)
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

    private function transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo, $additional)
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

        $extraInfo = [];
        if ($getUsersAddress) {
            foreach (['address_name', 'address', 'province_id','city_id', 'city_name', 'district_id', 'district_name',
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
            'send_info' => $additional,
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

    private function transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo, $additional)
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
        $klinikId = 0;
        foreach ($getData as $list) {
            $total += $list['price'];
            $klinikId = $list['klinik_id'];
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
            'klinik_id' => $klinikId ?? $getUser->klinik_id,
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
            'send_info' => $additional,
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

    private function transactionNurse($getNewCode, $getPaymentReferId, $getTypeService,$getServiceId,  $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();

        $getPayment = Payment::where('id', $getPaymentId)->first();

        $getReceiver = json_decode($getDetailsInfo, true);

        $data = BookNurse::where('user_id', $getUserId)->where('id', $getScheduleId)->first();
        if (!$data) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => 'Janji Temu Nurse Tidak Ditemukan'
            ]);
            $this->getJob->save();
            return;
        }
        $extraInfo = [];
        if ($getUsersAddress) {
            foreach (['address_name', 'address', 'province_id','city_id', 'city_name', 'district_id', 'district_name',
                         'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
                $extraInfo[$key] = isset($getUsersAddress->$key) ? $getUsersAddress->$key : '';
            }
        }

        $price = 50000;
        $total = $data->shift_qty*$price;

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $getNewCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getReceiver['receiver_name'] ?? '',
            'receiver_address' => $getReceiver['receiver_address'] ?? '',
            'receiver_phone' => $getReceiver['receiver_phone'] ?? '',
            'payment_info' => $getPaymentInfo,
            'category_service_id' => 0,
            'category_service_name' => '',
            'type_service' => 4,
            'type_service_name' => $getTypeService,
            'total_qty' => $data->shift_qty,
            'subtotal' => $total,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'send_info' => $additional,
            'status' => 2
        ]);

        TransactionDetails::create([
            'transaction_id' => $getTransaction->id,
            'schedule_id' => $getNurseInfo['id'],
            'nurse_id' => $getNurseInfo['id'],
            'nurse_shift' => $getNurseInfo['shift_qty'],
            'nurse_booked' => $getNurseInfo['date_booked']
        ]);

        $data->status = 80;
        $data->save();

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

}
