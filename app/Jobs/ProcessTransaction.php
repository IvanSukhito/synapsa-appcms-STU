<?php

namespace App\Jobs;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\LabLogic;
use App\Codes\Logic\ProductLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\BookNurse;
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
//                case 4 :
//                    $getScheduleId = isset($getJob['schedule_id']) ? intval($getJob['schedule_id']) : 0;
//                    $getNurseInfo = isset($getJob['nurse_info']) ? $getJob['nurse_info'] : [];
//                    $getDetailsInfo = [];
//                    $this->transactionNurse($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getScheduleId, $getNurseInfo, $getPaymentInfo, $getDetailsInfo, $additional);
//                    break;
                case 5 :
                    $getAppointmentDoctorId = isset($getJob['appointment_doctor_id']) ? intval($getJob['appointment_doctor_id']) : 0;
                    $getShippingId = isset($getJob['shipping_id']) ? intval($getJob['shipping_id']) : 0;
                    $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getShippingId, $getAppointmentDoctorId, $getPaymentInfo, $additional);
//                    $this->transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getType, $getUserId, $getPaymentId, $getShippingId, $getPaymentInfo, $getDetailsInfo, $getAppointmentDoctorId, $additional);
                    break;
            }
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
        $products = [];
        foreach ($getUsersCartDetail as $item) {
            $products[$item->product_id] = $item->qty;

            $transactionDetails[] = new TransactionDetails([
                'product_id' => $item->product_id,
                'product_name' => $item->name,
                'product_qty' => $item->qty,
                'product_price' => $item->price
            ]);

        }

        if (count($transactionDetails) > 0) {
            $productLogic = new ProductLogic();
            $productLogic->reduceStock($products);

            $getTransaction->getTransactionDetails()->saveMany($transactionDetails);
        }

        UsersCartDetail::where('users_cart_id', '=', $getCartInfo->id)
            ->where('choose', '=', 1)->delete();

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

    private function transactionProductKlinik($getNewCode, $getPaymentReferId, $getTypeService, $getServiceId, $getUserId, $getPaymentId, $getShippingId, $getAppointmentDoctorId, $getPaymentInfo, $additional)
    {
        $getUser = Users::where('id', '=', $getUserId)->first();
        $userLogic = new UserLogic();
        $getUserAddress = $userLogic->userAddress($getUserId, $getUser->phone);
        $getCart = $userLogic->userCartAppointmentDoctor($getUserId, $getAppointmentDoctorId, 1);
        if ($getCart['success'] == 0) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => $getCart['message']
            ]);
            $this->getJob->save();
            return;
        }

        $getShipping = Shipping::where('id', '=', $getShippingId)->first();
        if (!$getShipping) {
            $this->getJob->status = 99;
            $this->getJob->response = json_encode([
                'message' => 'Shipping tidak dipilih'
            ]);
            $this->getJob->save();
            return;
        }

        $getShippingId = $getShipping->id;
        $getShippingName = $getShipping->name;
        $getShippingPrice = $getShipping->price;

        $getUsersCartDetail = $getCart['data']['cart'];
        $getTotalQty = $getCart['data']['total_qty'] ?? 0;
        $getSubTotal = $getCart['data']['total'] ?? 0;
        $getTotal = $getSubTotal + $getShippingPrice;

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
        $products = [];
        foreach ($getUsersCartDetail as $item) {
            $products[$item->product_id] = $item->qty;

            $transactionDetails[] = new TransactionDetails([
                'product_id' => $item->product_id,
                'product_name' => $item->name,
                'product_qty' => $item->qty,
                'product_price' => $item->price
            ]);

        }

        if (count($transactionDetails) > 0) {
            $productLogic = new ProductLogic();
            $productLogic->reduceStock($products);

            $getTransaction->getTransactionDetails()->saveMany($transactionDetails);
        }

//        AppointmentDoctorProduct::where('users_cart_id', '=', $getCartInfo->id)
//            ->where('choose', '=', 1)->delete();

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

    /**
     * @param $getNewCode
     * @param $getPaymentReferId
     * @param $getTypeService
     * @param $getServiceId
     * @param $getUserId
     * @param $getPaymentId
     * @param $getScheduleId
     * @param $getDate
     * @param $getPaymentInfo
     * @param $additional
     */
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
        $serviceName = $getService ? $getService->name : '';

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
            'category_service_name' => $serviceName,
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

        $getDoctorName = $getDoctorInfo->doctor_name;
        $doctorLogic = new DoctorLogic();
        $getResult = $doctorLogic->appointmentCreate($getScheduleId, $getDate, $getUser, $getDoctorName,
            $serviceName, $getTransaction, $getTransaction->code, $extraInfo);

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

    /**
     * @param $getNewCode
     * @param $getPaymentReferId
     * @param $getTypeService
     * @param $getServiceId
     * @param $getUserId
     * @param $getPaymentId
     * @param $getScheduleId
     * @param $getDate
     * @param $getPaymentInfo
     * @param $additional
     * @param int $flag
     * @param int $transactionId
     */
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
            'type_service' => 3,
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
