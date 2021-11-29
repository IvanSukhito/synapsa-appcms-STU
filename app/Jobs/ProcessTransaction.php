<?php

namespace App\Jobs;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\LabLogic;
use App\Codes\Logic\UserLogic;
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
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\UsersCartDetail;
use Illuminate\Bus\Queueable;
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
        if ($this->getJob && $this->getJob->status == 1) {
            $getParams = json_decode($this->getJob->params, TRUE);
            $getPaymentReferId = $getParams['payment_refer_id'] ?? '';
            $getType = isset($getParams['type']) ? intval($getParams['type']) : 0;
            $getPaymentInfo = $getParams['payment_info'] ?? '';
            $additional = $getParams['additional'] ?? '';
            $getNewCode = $additional['code'] ?? '';

            $getJob = $additional['job'] ?? [];
            $getTypeService = $getJob['type_service'] ?? '';
            $getUserId = intval($getJob['user_id']) ?? 0;
            $getPaymentId = intval($getJob['payment_id']) ?? 0;
            $getServiceId = intval($getJob['service_id']) ?? 0;

            switch ($getType) {
                case 1 : $this->transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getPaymentInfo, $additional);
                    break;
                case 2 :
                    $getScheduleId = intval($getJob['schedule_id']) ?? 0;
                    $getDate = isset($getJob['date']) ? $getJob['date'] : date('Y-m-d');
                    $this->transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getScheduleId, $getDate, $getPaymentInfo, $additional);
                    break;
                case 3 :
                    $getScheduleId = isset($getJob['schedule_id']) ? intval($getJob['schedule_id']) : 0;
                    $getDate = isset($getJob['date']) ? $getJob['date'] : date('Y-m-d');
                    $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getScheduleId, $getDate, $getPaymentInfo, $additional);
//                    $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo, $additional);
                    break;
                case 4 :
                    $getScheduleId = isset($getJob['schedule_id']) ? intval($getJob['schedule_id']) : 0;
                    $getNurseInfo = isset($getJob['nurse_info']) ? $getJob['nurse_info'] : [];
                    $getDetailsInfo = [];
                    $this->transactionNurse($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional);
                    break;
                case 5 :
                    $getAppointmentDoctorId = isset($getParams['appointment_doctor_id']) ? intval($getParams['appointment_doctor_id']) : 0;
                    $getDetailsInfo = [];
                    $getShippingId = 0;
                    $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional);
                    break;
            }

//            if($this->flag == 1) {
//                if ($getType == 1) {
//                    $this->transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo, $additional, $this->flag, $this->transactionId);
//                }
//                else if ($getType == 2) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
//                    $this->transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo, $additional, $this->flag, $this->transactionId);
//                }
//                else if ($getType == 3) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getLabInfo = isset($getParams['lab_info']) ? $getParams['lab_info'] : [];
//                    $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo, $additional, $this->flag, $this->transactionId);
//                }
//                else if ($getType == 4) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getNurseInfo = isset($getParams['nurse_info']) ? $getParams['nurse_info'] : [];
//                    $this->transactionNurse($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional, $this->flag, $this->transactionId);
//                }
//                else if ($getType == 5) {
//                    $getAppointmentDoctorId = isset($getParams['appointment_doctor_id']) ? intval($getParams['appointment_doctor_id']) : 0;
//                    $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional, $this->flag, $this->transactionId);
//                }
//            }
//            else {
//                if ($getType == 1) {
//                    $this->transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getPaymentInfo, $additional);
//                } else if ($getType == 2) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getDoctorInfo = isset($getParams['doctor_info']) ? $getParams['doctor_info'] : [];
//                    $this->transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getDoctorInfo, $getPaymentInfo, $additional);
//                } else if ($getType == 3) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getLabInfo = isset($getParams['lab_info']) ? $getParams['lab_info'] : [];
//                    $this->transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getLabInfo, $getPaymentInfo, $additional);
//                } else if ($getType == 4) {
//                    $getScheduleId = isset($getParams['schedule_id']) ? intval($getParams['schedule_id']) : 0;
//                    $getNurseInfo = isset($getParams['nurse_info']) ? $getParams['nurse_info'] : [];
//                    $this->transactionNurse($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional);
//                } else if ($getType == 5) {
//                    $getAppointmentDoctorId = isset($getParams['appointment_doctor_id']) ? intval($getParams['appointment_doctor_id']) : 0;
//                    $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional);
//                }
//            }
        }
    }

    /**
     * @param $getNewCode
     * @param $getPaymentReferId
     * @param $getTypeService
     * @param $getServiceId
     * @param $getUserId
     * @param $getPaymentId
     * @param $getPaymentInfo
     * @param $additional
     */
    private function transactionProduct($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getPaymentInfo, $additional)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $userLogic = new UserLogic();
        $getUserAddress = $userLogic->userAddress($getUserId, $getUser->phone);
        $getCart = $userLogic->userCartProduct($getUserId, 1);

        $getCartInfo = $getCart['cart_info'];
        $getUsersCartDetail = $getCart['cart'];
        if ($getUsersCartDetail->count() <= 0) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Tidak ada Produk yang di pilih'
            ]);
            $this->getJob->save();
            return;
        }

        $getShippingId = $getCart['shipping_id'] ?? 0;
        $getShippingName = $getCart['shipping_name'] ?? '';
        $getShippingPrice = $getCart['shipping_price'] ?? '';
        $getTotalQty = $getCart['total_qty'] ?? 0;
        $getSubTotal = $getCart['subtotal'] ?? 0;
        $getTotal = $getCart['total'] ?? 0;
        if ($getShippingId <= 0) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Shipping tidak dipilih'
            ]);
            $this->getJob->save();
            return;
        }

        $getPayment = Payment::where('id', '=', $getPaymentId)->first();
        if (!$getPayment) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Payment tidak dipilih'
            ]);
            $this->getJob->save();
            return;
        }

        $newCode = date('Ym').$getNewCode;

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $getUser->klinik_id ?? 0,
            'user_id' => $getUser->id ?? 0,
            'code' => $newCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'shipping_id' => $getShippingId,
            'shipping_name' => $getShippingName,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getUserAddress['address_name'] ?? '',
            'receiver_address' => $getUserAddress['address'] ?? '',
            'receiver_phone' => $getUser->phone ?? '',
            'shipping_address_name' => $getUserAddress['address_name'] ?? '',
            'shipping_address' => $getUserAddress['address'] ?? '',
            'shipping_province_id' => $getUserAddress['province_id'] ?? '',
            'shipping_province_name' => $getUserAddress['province_name'] ?? '',
            'shipping_city_id' => $getUserAddress['city_id'] ?? '',
            'shipping_city_name' => $getUserAddress['city_name'] ?? '',
            'shipping_district_id' => $getUserAddress['district_id'] ?? '',
            'shipping_district_name' => $getUserAddress['district_name'] ?? '',
            'shipping_subdistrict_id' => $getUserAddress['sub_district_id'] ?? '',
            'shipping_subdistrict_name' => $getUserAddress['sub_district_name'] ?? '',
            'shipping_zipcode' => $getUserAddress['zip_code'] ?? '',
            'send_info' => json_encode($additional),
            'payment_info' => json_encode($getPaymentInfo),
            'category_service_id' => 0,
            'category_service_name' => '',
            'type_service' => 1,
            'type_service_name' => $getTypeService,
            'total_qty' => $getTotalQty,
            'subtotal' => $getSubTotal,
            'shipping_price' => $getShippingPrice,
            'total' => $getTotal,
            'status' => 2
        ]);

        $transactionDetails = [];
        $UsersCartDetailId = [];
        foreach ($getUsersCartDetail as $item) {
            $UsersCartDetailId[] = $item->id;
            $item->stock -= $item->qty;
            $item->save();

            $transactionDetails[] = new TransactionDetails([
                'product_id' => $item->product_id,
                'product_name' => $item->name,
                'product_qty' => $item->qty,
                'product_price' => $item->price
            ]);

        }

        if (count($transactionDetails) > 0) {
            $getTransaction->getTransactionDetails()->saveMany($transactionDetails);
        }

        UsersCartDetail::whereIn('users_cart_id', '=', $getCartInfo->id)
            ->where('choose', '=', 1)->delete();

        DB::commit();

        Log::info("ok");

        $this->getJob->status = 80;
        $this->getJob->response = json_encode([
            'service' => $getTypeService,
            'service_id' => $getServiceId,
            'transaction_id' => $getTransaction->id,
            'message' => 'ok'
        ]);
        $this->getJob->save();

//        $getUser = Users::where('id', $getUserId)->first();
//        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
//        $getPayment = Payment::where('id', $getPaymentId)->first();
//        $getUsersCart = UsersCart::firstOrCreate([
//            'users_id' => $getUserId,
//        ]);
//
//        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
//        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
//        $getShippingId = $getDetailsShipping['shipping_id'];
//        $getShipping = Shipping::where('id', $getShippingId)->first();
//        if (!$getShipping) {
//            $this->getJob->status = 99;
//            $this->getJob->response = json_encode([
//                'service' => $getTypeService,
//                'service_id' => $getServiceId,
//                'message' => 'Pengiriman Tidak Ditemukan'
//            ]);
//            $this->getJob->save();
//            return;
//        }
//
//        if($flag == 1) {
//            $getTransaction = Transaction::where('id', $transactionId)->where('status', 2)->first();
//            if (!$getTransaction) {
//                $this->getJob->status = 99;
//                $this->getJob->response = json_encode([
//                    'transaction_id' => $transactionId,
//                    'service' => $getTypeService,
//                    'service_id' => $getServiceId,
//                    'message' => 'Transaksi Tidak Ditemukan'
//                ]);
//                $this->getJob->save();
//                return;
//            }
//        }
//        else {
//            $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
//                ->where('choose', 1)->count();
//            if ($getUsersCartDetail <= 0) {
//                $this->getJob->status = 99;
//                $this->getJob->response = json_encode([
//                    'service' => $getTypeService,
//                    'service_id' => $getServiceId,
//                    'message' => 'Tidak ada Produk yang di pilih'
//                ]);
//                $this->getJob->save();
//                return;
//            }
//        }
//
//        $newCode = date('Ym').$getNewCode;
//
//        DB::beginTransaction();
//
//        if($flag == 1) {
//            $getTransaction->code = $newCode;
//            $getTransaction->payment_refer_id = $getPaymentReferId;
//            $getTransaction->payment_service = $getPayment->service;
//            $getTransaction->type_payment = $getPayment->type_payment;
//            $getTransaction->payment_id = $getPaymentId;
//            $getTransaction->payment_name = $getPayment->name;
//            $getTransaction->send_info = $additional;
//            $getTransaction->payment_info = $getPaymentInfo;
//            $getTransaction->status = 2;
//            $getTransaction->save();
//        }
//        else {
//            $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.id AS product_id, product.name AS product_name,
//            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
//                ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
//                ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
//                ->where('choose', 1)->get();
//
//            $totalQty = 0;
//            $subTotal = 0;
//            $shippingPrice = $getShipping->price;
//            $transactionDetails = [];
//            $productQty = [];
//            $getProductIds = [];
//            foreach ($getUsersCartDetails as $list) {
//                // dd($list->qty);
//                $totalQty += $list->qty;
//
//                $subTotal += ($list->qty * $list->price);
//
//                $productQty[] = $list->qty;
//                $getProductIds[] = $list->product_id;
//                $transactionDetails[] = new TransactionDetails([
//                    'product_id' => $list->product_id,
//                    'product_name' => $list->product_name,
//                    'product_qty' => $list->qty,
//                    'product_price' => $list->price
//                ]);
//            }
//
//            $total = $subTotal + $shippingPrice;
//
//            $getProducts = Product::whereIn('id', $getProductIds)->get();
//
//            foreach ($getProducts as $key => $list) {
//
//                $qty = $productQty[$key];
//
//                if ($list->stock_flag == 2) {
//                    $list->stock = $list->stock - $qty;
//                    $list->save();
//
//                    Product::where('parent_id', $list->id)->update([
//                        'stock' => $list->stock,
//                    ]);
//
//                    if ($list->klinik_id > 0 && $list->parent_id > 0) {
//                        $productParent = Product::where('id', $list->parent_id)->first();
//                        $productParent->stock = $list->stock;
//                        $productParent->save();
//
//                        Product::where('parent_id', $productParent->id)->update([
//                            'stock' => $list->stock,
//                        ]);
//                    }
//                }
//            }
//
//            $getTransaction = Transaction::create([
//                'klinik_id' => $getUser->klinik_id,
//                'user_id' => $getUser->id,
//                'code' => $newCode,
//                'payment_refer_id' => $getPaymentReferId,
//                'payment_service' => $getPayment->service,
//                'type_payment' => $getPayment->type_payment,
//                'shipping_id' => $getShippingId,
//                'shipping_name' => $getShipping->name,
//                'payment_id' => $getPaymentId,
//                'payment_name' => $getPayment->name,
//                'receiver_name' => $getDetailsInformation['receiver'] ?? '',
//                'receiver_address' => $getDetailsInformation['address'] ?? '',
//                'receiver_phone' => $getDetailsInformation['phone'] ?? '',
//                'shipping_address_name' => $getUsersAddress->address_name ?? '',
//                'shipping_address' => $getUsersAddress->address ?? '',
//                'shipping_province_id' => $getUsersAddress->province_id ?? 0,
//                'shipping_province_name' => $getUsersAddress->province_name ?? '',
//                'shipping_city_id' => $getUsersAddress->city_id ?? 0,
//                'shipping_city_name' => $getUsersAddress->city_name ?? '',
//                'shipping_district_id' => $getUsersAddress->district_id ?? 0,
//                'shipping_district_name' => $getUsersAddress->district_name ?? '',
//                'shipping_subdistrict_id' => $getUsersAddress->sub_district_id ?? 0,
//                'shipping_subdistrict_name' => $getUsersAddress->sub_district_name ?? '',
//                'shipping_zipcode' => $getUsersAddress->zip_code ?? '',
//                'send_info' => $additional,
//                'payment_info' => $getPaymentInfo,
//                'category_service_id' => 0,
//                'category_service_name' => '',
//                'type_service' => 1,
//                'type_service_name' => $getTypeService,
//                'total_qty' => $totalQty,
//                'subtotal' => $subTotal,
//                'shipping_price' => $shippingPrice,
//                'total' => $total,
//                'status' => 2
//            ]);
//
//            $getTransaction->getTransactionDetails()->saveMany($transactionDetails);
//
//            UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
//                ->where('choose', 1)->delete();
//        }
//
//        DB::commit();

    }

    private function transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional, $flag = 0, $transactionId = 0)
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

        $getUsersCartDetails = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty as product_qty, product_qty_checkout, appointment_doctor_product.choose')
            ->join('product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $getAppointmentDoctorId)
            ->where('choose', '=', 1)
            ->get();

        $newCode = date('Ym').$getNewCode;

        DB::beginTransaction();

        $totalQty = 0;
        $subTotal = 0;
        $shippingPrice = $getShipping->price;
        $transactionDetails = [];
        $productQty = [];
        $getProductIds = [];
        foreach ($getUsersCartDetails as $list) {

            $list->product_qty -= $list->product_qty_checkout;
            $list->save();

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
                if($list->klinik_id > 0 && $list->parent_id > 0) {
                    $productParent = Product::where('id', $list->parent_id)->first();
                    $productParent->stock = $productParent->stock - $qty;
                    $productParent->save();

                    Product::where('parent_id', $productParent->id)->update([
                        'stock' => $productParent->stock,
                    ]);
                }

                $list->stock = $list->stock - $qty;
                $list->save();

                Product::where('parent_id', $list->id)->update([
                    'stock' => $list->stock,
                ]);
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
            'send_info' => json_encode($additional),
            'payment_info' => json_encode($getPaymentInfo),
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

    private function transactionDoctor($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getScheduleId, $getDate, $getPaymentInfo, $additional)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $doctorLogic = new DoctorLogic();
        $getDoctorSchedule = $doctorLogic->scheduleCheck($getScheduleId, $getDate, $getUserId, 1);
        if ($getDoctorSchedule['success'] != 80) {
            if ($getDoctorSchedule['success'] == 90) {
                $message = 'Jadwal Tidak Ditemukan';
            }
            else if ($getDoctorSchedule['success'] == 91) {
                $message = 'Jadwal Tidak tersedia';
            }
            else if ($getDoctorSchedule['success'] == 92) {
                $message = 'Jadwal Sudah Dipesan';
            }
            else if ($getDoctorSchedule['success'] == 93) {
                $message = 'Waktu date tidak sama';
            }
            else {
                $message = 'Doctor Error';
            }

            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => $message
            ]);
            $this->getJob->save();
            return;

        }

        $getSchedule = $getDoctorSchedule['schedule'];
        $doctorId = $getSchedule->doctor_id;
        $serviceId = $getSchedule->service_id;
        $getDoctorInfo = $doctorLogic->doctorInfo($doctorId, $serviceId);
        $getService = Service::where('id', '=', $serviceId)->first();

        $subTotal = $getDoctorInfo->price;
        $total = $subTotal;

        $getPayment = Payment::where('id', '=', $getPaymentId)->first();
        if (!$getPayment) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Payment tidak dipilih'
            ]);
            $this->getJob->save();
            return;
        }

        $newCode = date('Ym').$getNewCode;

        $extraInfo = [
            'service_id' => $serviceId,
            'service_name' => $getService->name,
            'date' => $getDate,
            'phone' => $getUser->phone ?? '',
            'need_address' => $getService->type == 2 ? 1 : 0
        ];

        if ($getService->type == 1) {
            $getJob = $additional['job'] ?? [];
            $getSubServiceId = intval($getJob['sub_service_id']) ?? 0;
            $getList = get_list_sub_service2();
            $extraInfo['sub_service_id'] = $getSubServiceId;
            $extraInfo['sub_service_name'] = $getList[$getSubServiceId] ?? '';
        }

        $saveData = [
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $newCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getUser->fullname,
            'receiver_address' => $getUser->address ?? '',
            'receiver_phone' => $getUser->phone ?? '',
            'extra_info' => json_encode($extraInfo),
            'send_info' => json_encode($additional),
            'payment_info' => json_encode($getPaymentInfo),
            'category_service_id' => $serviceId,
            'category_service_name' => $getService ? $getService->name : '',
            'type_service' => 2,
            'type_service_name' => $getTypeService,
            'total_qty' => 1,
            'subtotal' => $subTotal,
            'total' => $total,
            'status' => 2
        ];

        if ($getService->type == 2) {
            $userLogic = new UserLogic();
            $getUserAddress = $userLogic->userAddress($getUserId, $getUser->phone);
            $saveData['shipping_address_name'] = $getUserAddress['address_name'] ?? '';
            $saveData['shipping_address'] = $getUserAddress['address'] ?? '';
            $saveData['shipping_province_id'] = $getUserAddress['province_id'] ?? '';
            $saveData['shipping_province_name'] = $getUserAddress['province_name'] ?? '';
            $saveData['shipping_city_id'] = $getUserAddress['city_id'] ?? '';
            $saveData['shipping_city_name'] = $getUserAddress['city_name'] ?? '';
            $saveData['shipping_district_id'] = $getUserAddress['district_id'] ?? '';
            $saveData['shipping_district_name'] = $getUserAddress['district_name'] ?? '';
            $saveData['shipping_subdistrict_id'] = $getUserAddress['sub_district_id'] ?? '';
            $saveData['shipping_subdistrict_name'] = $getUserAddress['sub_district_name'] ?? '';
            $saveData['shipping_zipcode'] = $getUserAddress['zip_code'] ?? '';
        }

        DB::beginTransaction();

        $getTransaction = Transaction::create($saveData);

        TransactionDetails::create([
            'transaction_id' => $getTransaction->id,
            'schedule_id' => $getScheduleId,
            'doctor_id' => $doctorId,
            'doctor_name' => $getDoctorInfo->doctor_name,
            'doctor_price' => $subTotal,
            'extra_info' => json_encode($extraInfo)
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

    private function transactionLab($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getScheduleId, $getDate, $getPaymentInfo, $additional, $flag = 0, $transactionId = 0)
    {
        $getUser = Users::where('id', $getUserId)->first();
        $labLogic = new LabLogic();
        $userLogic = new UserLogic();
        $getLabSchedule = $labLogic->scheduleCheck($getScheduleId, $getDate, 1);
        if ($getLabSchedule['success'] != 80) {
            if ($getLabSchedule['success'] == 90) {
                $message = 'Jadwal Tidak Ditemukan';
            }
            else if ($getLabSchedule['success'] == 93) {
                $message = 'Waktu date tidak sama';
            }
            else {
                $message = 'Lab Error';
            }

            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'service' => $getTypeService,
                'service_id' => $getServiceId,
                'message' => $message
            ]);
            $this->getJob->save();
            return;

        }

        $getSchedule = $getLabSchedule['schedule'];
        $getDate = $getLabSchedule['date'];
        $getTime = $getLabSchedule['time'];
        $getService = Service::where('id', '=', $getServiceId)->first();

        $getLabCart = $userLogic->userCartLab($getUserId, 1);
        $getCart = $getLabCart['cart'];
        $subTotal = $getLabCart['total'];
        $total = $subTotal;

        $getPayment = Payment::where('id', '=', $getPaymentId)->first();
        if (!$getPayment) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Payment tidak dipilih'
            ]);
            $this->getJob->save();
            return;
        }

        $newCode = date('Ym').$getNewCode;

        $extraInfo = [
            'service_id' => $getServiceId,
            'service_name' => $getService->name,
            'date' => $getDate,
            'phone' => $getUser->phone ?? '',
            'need_address' => $getService->type == 2 ? 1 : 0
        ];

        if ($getService->type == 1) {
            $getJob = $additional['job'] ?? [];
            $getSubServiceId = intval($getJob['sub_service_id']) ?? 0;
            $getList = get_list_sub_service2();
            $extraInfo['sub_service_id'] = $getSubServiceId;
            $extraInfo['sub_service_name'] = $getList[$getSubServiceId] ?? '';
        }

        $saveData = [
            'klinik_id' => $getUser->klinik_id,
            'user_id' => $getUser->id,
            'code' => $newCode,
            'payment_refer_id' => $getPaymentReferId,
            'payment_service' => $getPayment->service,
            'type_payment' => $getPayment->type_payment,
            'payment_id' => $getPaymentId,
            'payment_name' => $getPayment->name,
            'receiver_name' => $getUser->fullname,
            'receiver_address' => $getUser->address ?? '',
            'receiver_phone' => $getUser->phone ?? '',
            'extra_info' => json_encode($extraInfo),
            'send_info' => json_encode($additional),
            'payment_info' => json_encode($getPaymentInfo),
            'category_service_id' => $getServiceId,
            'category_service_name' => $getService ? $getService->name : '',
            'type_service' => 2,
            'type_service_name' => $getTypeService,
            'total_qty' => 1,
            'subtotal' => $subTotal,
            'total' => $total,
            'status' => 2
        ];

        if ($getService->type == 2) {
            $userLogic = new UserLogic();
            $getUserAddress = $userLogic->userAddress($getUserId, $getUser->phone);
            $saveData['shipping_address_name'] = $getUserAddress['address_name'] ?? '';
            $saveData['shipping_address'] = $getUserAddress['address'] ?? '';
            $saveData['shipping_province_id'] = $getUserAddress['province_id'] ?? '';
            $saveData['shipping_province_name'] = $getUserAddress['province_name'] ?? '';
            $saveData['shipping_city_id'] = $getUserAddress['city_id'] ?? '';
            $saveData['shipping_city_name'] = $getUserAddress['city_name'] ?? '';
            $saveData['shipping_district_id'] = $getUserAddress['district_id'] ?? '';
            $saveData['shipping_district_name'] = $getUserAddress['district_name'] ?? '';
            $saveData['shipping_subdistrict_id'] = $getUserAddress['sub_district_id'] ?? '';
            $saveData['shipping_subdistrict_name'] = $getUserAddress['sub_district_name'] ?? '';
            $saveData['shipping_zipcode'] = $getUserAddress['zip_code'] ?? '';
        }

        DB::beginTransaction();

        $getTransaction = Transaction::create($saveData);

        foreach ($getCart as $list) {
            TransactionDetails::create([
                'transaction_id' => $getTransaction->id,
                'schedule_id' => $getScheduleId,
                'lab_id' => $list['id'],
                'lab_name' => $list['name'],
                'lab_price' => $list['price'],
                'extra_info' => json_encode($extraInfo)
            ]);
        }

        LabCart::where('user_id', '=', $getUserId)
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

        return;
//
//
//
//        $getLabSchedule = LabSchedule::where('id', $getScheduleId)->first();
//        if (!$getLabSchedule) {
//            $this->getJob->status = 99;
//            $this->getJob->response = json_encode([
//                'service' => $getTypeService,
//                'service_id' => $getServiceId,
//                'message' => 'Jadwal Tidak Ditemukan'
//            ]);
//            $this->getJob->save();
//            return;
//        }
//
//        if($flag == 1) {
//            $getTransaction = Transaction::where('id', $transactionId)->where('status', 2)->first();
//            if (!$getTransaction) {
//                $this->getJob->status = 99;
//                $this->getJob->response = json_encode([
//                    'transaction_id' => $transactionId,
//                    'service' => $getTypeService,
//                    'service_id' => $getServiceId,
//                    'message' => 'Transaksi Tidak Ditemukan'
//                ]);
//                $this->getJob->save();
//                return;
//            }
//        }
//
//        $getData = $getLabInfo;
//        $total = 0;
//        $klinikId = 0;
//        foreach ($getData as $list) {
//            $total += $list['price'];
//            $klinikId = $list['klinik_id'];
//        }
//
//        $getUsersAddress = UsersAddress::where('user_id', $getUserId)->first();
//        $getPayment = Payment::where('id', $getPaymentId)->first();
//        $getUser = Users::where('id', $getUserId)->first();
//        $getService = Service::where('id', $getLabSchedule->service_id)->first();
//
//        $newCode = date('Ym').$getNewCode;
//
//        $extraInfo = [
//            'service_id' => $getLabSchedule->service_id,
//            'phone' => $getUser->phone ?? ''
//        ];
//
//        if ($getUsersAddress) {
//            foreach (['address_name', 'address', 'city_id', 'city_name', 'district_id', 'district_name',
//                         'sub_district_id', 'sub_district_name', 'zip_code'] as $key) {
//                $extraInfo[$key] = isset($getUsersAddress->$key) ? $getUsersAddress->$key : '';
//            }
//        }
//        DB::beginTransaction();
//
//        if($flag == 1) {
//            $getTransaction->code = $newCode;
//            $getTransaction->payment_refer_id = $getPaymentReferId;
//            $getTransaction->payment_service = $getPayment->service;
//            $getTransaction->type_payment = $getPayment->type_payment;
//            $getTransaction->payment_id = $getPaymentId;
//            $getTransaction->payment_name = $getPayment->name;
//            $getTransaction->send_info = $additional;
//            $getTransaction->payment_info = $getPaymentInfo;
//            $getTransaction->status = 2;
//            $getTransaction->save();
//        }
//        else {
//            $getTransaction = Transaction::create([
//                'klinik_id' => $klinikId ?? $getUser->klinik_id,
//                'user_id' => $getUser->id,
//                'code' => $newCode,
//                'payment_refer_id' => $getPaymentReferId,
//                'payment_service' => $getPayment->service,
//                'type_payment' => $getPayment->type_payment,
//                'payment_id' => $getPaymentId,
//                'payment_name' => $getPayment->name,
//                'payment_info' => json_encode($getPaymentInfo),
//                'category_service_id' => $getLabSchedule->service_id,
//                'category_service_name' => $getService ? $getService->name : '',
//                'type_service' => 3,
//                'type_service_name' => $getTypeService,
//                'subtotal' => $total,
//                'total' => $total,
//                'extra_info' => json_encode($extraInfo),
//                'send_info' => json_encode($additional),
//                'status' => 2
//            ]);
//
//            $listTransactionDetails = [];
//            foreach ($getData as $list) {
//                $getTransactionDetails = TransactionDetails::create([
//                    'transaction_id' => $getTransaction->id,
//                    'schedule_id' => $getScheduleId,
//                    'lab_id' => $list['lab_id'],
//                    'lab_name' => $list['name'],
//                    'lab_price' => $list['price']
//                ]);
//                $listTransactionDetails[] = $getTransactionDetails;
//            }
//
//            LabCart::where('user_id', $getUser->id)->where('choose', '=', 1)->delete();
//        }
//
//        DB::commit();
//
//        $this->getJob->status = 80;
//        $this->getJob->response = json_encode([
//            'service' => $getTypeService,
//            'service_id' => $getServiceId,
//            'transaction_id' => $getTransaction->id,
//            'message' => 'ok'
//        ]);
//        $this->getJob->save();
    }

    private function transactionNurse($getNewCode, $getPaymentReferId, $getTypeService,$getServiceId,  $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional, $flag = 0, $transactionId = 0)
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

        if($flag == 1) {
            $getTransaction = Transaction::where('id', $transactionId)->where('status', 2)->first();
            if (!$getTransaction) {
                $this->getJob->status = 99;
                $this->getJob->response = json_encode([
                    'transaction_id' => $transactionId,
                    'service' => $getTypeService,
                    'service_id' => $getServiceId,
                    'message' => 'Transaksi Tidak Ditemukan'
                ]);
                $this->getJob->save();
                return;
            }
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

        $newCode = date('Ym').$getNewCode;

        DB::beginTransaction();

        if($flag == 1) {
            $getTransaction->code = $newCode;
            $getTransaction->payment_refer_id = $getPaymentReferId;
            $getTransaction->payment_service = $getPayment->service;
            $getTransaction->type_payment = $getPayment->type_payment;
            $getTransaction->payment_id = $getPaymentId;
            $getTransaction->payment_name = $getPayment->name;
            $getTransaction->send_info = json_encode($additional);
            $getTransaction->payment_info = json_encode($getPaymentInfo);
            $getTransaction->status = 2;
            $getTransaction->save();
        }
        else {
            $getTransaction = Transaction::create([
                'klinik_id' => $getUser->klinik_id,
                'user_id' => $getUser->id,
                'code' => $newCode,
                'payment_refer_id' => $getPaymentReferId,
                'payment_service' => $getPayment->service,
                'type_payment' => $getPayment->type_payment,
                'payment_id' => $getPaymentId,
                'payment_name' => $getPayment->name,
                'receiver_name' => $getReceiver['receiver_name'] ?? '',
                'receiver_address' => $getReceiver['receiver_address'] ?? '',
                'receiver_phone' => $getReceiver['receiver_phone'] ?? '',
                'payment_info' => json_encode($getPaymentInfo),
                'category_service_id' => 0,
                'category_service_name' => '',
                'type_service' => 4,
                'type_service_name' => $getTypeService,
                'total_qty' => $data->shift_qty,
                'subtotal' => $total,
                'total' => $total,
                'extra_info' => json_encode($extraInfo),
                'send_info' => json_encode($additional),
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
        }

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
