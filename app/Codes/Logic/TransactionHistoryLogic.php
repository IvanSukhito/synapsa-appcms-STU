<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionHistoryLogic
{
    public function __construct()
    {
    }

    /**
     * @param $userId
     * @param $limit
     * @param $search
     * @return array
     */
    public function productHistory($userId, $limit, $search): array
    {
        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name, transaction.total_qty, transaction.subtotal, transaction.total,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            MIN(product_name) AS product_name, MIN(product.image) as image,
            CONCAT("'.env('OSS_URL').'/'.'", MIN(product.image)) AS image_full')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                    ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('product', function($join){
                $join->on('product.id','=','transaction_details.product_id')
                    ->on('product.id', '=', DB::raw("(select min(id) from product WHERE product.id = transaction_details.product_id)"));
            })
            ->where('transaction.user_id', '=', $userId)
            ->whereIn('type_service', [1,5])->orderBy('transaction.id','DESC');

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('product_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name, transaction.total_qty, transaction.subtotal, transaction.total,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($limit);

        return [
            'data' => $getData,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    /**
     * @param $userId
     * @param $serviceId
     * @param $subServiceId
     * @param $limit
     * @param $search
     * @return array
     */
    public function doctorHistory($userId, $serviceId, $subServiceId, $limit, $search): array
    {
        $doctorLogic = new DoctorLogic();
        $getService = $doctorLogic->getListService($serviceId, $subServiceId);

        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            doctor_category.name as category_doctor, MIN(doctor_name) AS doctor_name,
            MIN(users.image) AS image, CONCAT("'.env('OSS_URL').'/'.'", MIN(users.image)) AS image_full')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id','=','transaction.id')
            ->leftJoin('doctor', 'doctor.id','=','transaction_details.doctor_id')
            ->leftJoin('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->leftJoin('users', 'users.id','=','doctor.user_id')
            ->where('transaction.user_id', '=', $userId)
            ->where('type_service', '=', 2);

        if ($serviceId > 0) {
            $getData = $getData->where('category_service_id', '=', $serviceId);
        }

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('doctor_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
                transaction.category_service_id, transaction.category_service_name,
                transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
                category_doctor')
            ->paginate($limit);

        return [
            'data' => $getData,
            'service' => $getService['data'],
            'sub_service' => $getService['sub_service'],
            'active' => [
                'service' => $getService['getServiceId'],
                'service_name' => $getService['getServiceName'],
                'sub_service' => $getService['getSubServiceId'],
                'sub_service_name' => $getService['getSubServiceName'],
            ],
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    /**
     * @param $userId
     * @param $serviceId
     * @param $subServiceId
     * @param $limit
     * @param $search
     * @return array
     */
    public function labHistory($userId, $serviceId, $subServiceId, $limit, $search): array
    {
        $labLogic = new LabLogic();
        $getService = $labLogic->getListService($serviceId, $subServiceId);

        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            MIN(lab_name) AS lab_name, MIN(lab.image) as image, CONCAT("'.env('OSS_URL').'/'.'", MIN(lab.image)) AS image_full')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                    ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('lab', function($join){
                $join->on('lab.id','=','transaction_details.lab_id')
                    ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = transaction_details.lab_id)"));
            })
            ->where('transaction.user_id', '=', $userId)
            ->where('type_service', '=', 3);

        if ($serviceId > 0) {
            $getData = $getData->where('category_service_id', '=', $serviceId);
        }

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('lab_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($limit);

        return [
            'data' => $getData,
            'service' => $getService['data'],
            'sub_service' => $getService['sub_service'],
            'active' => [
                'service' => $getService['getServiceId'],
                'service_name' => $getService['getServiceName'],
                'sub_service' => $getService['getSubServiceId'],
                'sub_service_name' => $getService['getSubServiceName'],
            ],
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    /**
     * @param $userId
     * @param $transactionId
     * @return array
     */
    public function detailHistory($userId, $transactionId): array
    {
        $getTransaction = Transaction::where('id', '=', $transactionId)->where('user_id', '=', $userId)->first();
        if ($getTransaction) {
            switch ($getTransaction->type_service) {
                case 2 : $getDetail = $getTransaction->getTransactionDetails()->selectRaw('transaction_details.id,
                        transaction_details.schedule_id, transaction_details.doctor_id, transaction_details.doctor_name, 
                        transaction_details.doctor_price, transaction_details.extra_info, 
                        doctor_category.name AS doctor_category_name,
                        appointment_doctor.date, appointment_doctor.time_start, appointment_doctor.time_end,
                        klinik.name as klinik_name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, 
                        CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                    ->join('doctor', 'doctor.id', '=', 'transaction_details.doctor_id', 'LEFT')
                    ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id', 'LEFT')
                    ->join('users', 'users.id', '=', 'doctor.user_id', 'LEFT')
                    ->join('klinik', 'klinik.id', '=', 'users.klinik_id')
                    ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                    ->join('appointment_doctor','appointment_doctor.transaction_id','=','transaction_details.transaction_id', 'LEFT')
                    ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                    ->first();
                    $setType = 'doctor';
                    break;

                case 3 : $getDetail = $getTransaction->getTransactionDetails()->selectRaw('transaction_details.id,
                        transaction_details.schedule_id, transaction_details.lab_id, transaction_details.lab_name, 
                        transaction_details.lab_price, transaction_details.extra_info, 
                        appointment_lab.date, appointment_lab.time_start, appointment_lab.time_end,
                        lab.image, CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, 
                        CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                    ->join('lab', 'lab.id', '=', 'transaction_details.lab_id', 'LEFT')
                    ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                    ->join('appointment_lab','appointment_lab.transaction_id','=','appointment_lab.transaction_id', 'LEFT')
                    ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                    ->get();
                    $setType = 'lab';
                    break;

                default: $getDetail = $getTransaction->getTransactionDetails()->selectRaw('transaction_details.id,
                        transaction_details.product_id, transaction_details.product_name, transaction_details.product_qty,
                        transaction_details.product_price,
                        product.image, CONCAT("'.env('OSS_URL').'/'.'", product.image) AS image_full, 
                        CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                    ->join('product', 'product.id', '=', 'transaction_details.product_id', 'LEFT')
                    ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                    ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                    ->get();
                    $setType = 'product';
                    break;
            }

            $paymentInfo = json_decode($getTransaction->payment_info, TRUE);
            $userAddress = [
                'receiver_name' => $getTransaction->receiver_name,
                'receiver_address' => $getTransaction->receiver_address,
                'receiver_phone' => $getTransaction->receiver_phone,
                'shipping_address_name' => $getTransaction->shipping_address_name,
                'shipping_address' => $getTransaction->shipping_address,
                'shipping_province_id' => $getTransaction->shipping_province_id,
                'shipping_province_name' => $getTransaction->shipping_province_name,
                'shipping_city_id' => $getTransaction->shipping_city_id,
                'shipping_city_name' => $getTransaction->shipping_city_name,
                'shipping_district_id' => $getTransaction->shipping_district_id,
                'shipping_district_name' => $getTransaction->shipping_district_name,
                'shipping_subdistrict_id' => $getTransaction->shipping_subdistrict_id,
                'shipping_subdistrict_name' => $getTransaction->shipping_subdistrict_name,
                'shipping_zipcode' => $getTransaction->shipping_zipcode
            ];

            $getTransactionArray = $getTransaction->toArray();

            unset($getTransactionArray['payment_info']);
            unset($getTransactionArray['extra_info']);
            unset($getTransactionArray['send_info']);
            unset($getTransactionArray['receiver_name']);
            unset($getTransactionArray['receiver_address']);
            unset($getTransactionArray['receiver_phone']);
            unset($getTransactionArray['shipping_address_name']);
            unset($getTransactionArray['shipping_address']);
            unset($getTransactionArray['shipping_province_id']);
            unset($getTransactionArray['shipping_province_name']);
            unset($getTransactionArray['shipping_city_id']);
            unset($getTransactionArray['shipping_city_name']);
            unset($getTransactionArray['shipping_district_id']);
            unset($getTransactionArray['shipping_district_name']);
            unset($getTransactionArray['shipping_subdistrict_id']);
            unset($getTransactionArray['shipping_subdistrict_name']);
            unset($getTransactionArray['shipping_zipcode']);

            return [
                'success' => 1,
                'data' => [
                    'transaction' => $getTransactionArray,
                    'type' => $setType,
                    'details' => $getDetail,
                    'payment_info' => $paymentInfo,
                    'user_address' => $userAddress
                ]
            ];
        }

        return [
            'success' => 0
        ];

    }

    /**
     * @param $userId
     * @param $transactionId
     * @return array
     */
    public function reTriggerPayment($userId, $transactionId): array
    {
        $getTransaction = Transaction::where('id', '=', $transactionId)->where('user_id', '=', $userId)->first();
        if ($getTransaction) {
            if ($getTransaction->status == 2) {
                $getPayment = Payment::where('id', $getTransaction->payment_id)->first();
                if ($getPayment && $getPayment->service == 'xendit' && (substr($getPayment->type_payment, 0, 3) == 'ew_' || $getPayment->type_payment == 'qr_qris')) {
                    $synapsaLogic = new SynapsaLogic();

                    $getNewCode = str_pad(($getTransaction->id), 6, '0', STR_PAD_LEFT).rand(100000,999999);

                    $getAdditional = json_decode($getTransaction->send_info, true);
                    $getAdditional['code'] = $getNewCode;
                    $getAdditional['job']['code'] = $getNewCode;

                    $getPaymentInfo = $synapsaLogic->createPayment($getPayment, $getAdditional, $transactionId);
                    if ($getPaymentInfo['success'] == 1) {
                        return [
                            'success' => 80,
                            'data' => [
                                'payment' => 0,
                                'info' => $getPaymentInfo['info']
                            ]
                        ];
                    }
                }

                return [
                    'success' => 92
                ];

            }

            return [
                'success' => 91
            ];

        }

        return [
            'success' => 90
        ];

    }

}
