<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class HistoryController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 10;
    }

    public function index()
    {
        $user = $this->request->attributes->get('_user');

        $getServiceId = intval($this->request->get('service_id'));
        $getDoctor = intval($this->request->get('doctor'));
        $getLab = intval($this->request->get('lab'));
        $getProduct = intval($this->request->get('product'));
        $getNurse = intval($this->request->get('nurse'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = intval($this->request->get('limit'));

        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        if ($getDoctor == 1) {
            $getData = $this->getListDoctor($user->id, $getServiceId, $getLimit, $s);
            $getProduct = 0;
            $getLab = 0;
            $getNurse = 0;
        }
        elseif ($getLab == 1) {
            $getData = $this->getListLab($user->id, $getServiceId, $getLimit, $s);
            $getProduct = 0;
            $getDoctor = 0;
            $getNurse = 0;
        }
        elseif ($getProduct == 1) {
            $getData = $this->getListProduct($user->id, $getLimit, $s);
            $getDoctor = 0;
            $getLab = 0;
            $getNurse = 0;
        }
        elseif ($getNurse == 1){
            $getData = $this->getListNurse($user->id, $getLimit);
            $getDoctor = 0;
            $getLab = 0;
            $getProduct = 0;
        }
        else {
            $getData = $this->getListProduct($user->id, $getLimit, $s);
            $getProduct = 1;
            $getDoctor = 0;
            $getLab = 0;
            $getNurse = 0;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $getData['data'],
                'service' => $getData['service'] ?? [],
                'default_image' => $getData['default_image'] ?? '',
                'active' => [
                    'doctor' => $getDoctor,
                    'product' => $getProduct,
                    'lab' => $getLab,
                    'nurse' => $getNurse
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $getData = Transaction::where('id', $id)
            ->where('user_id',$user->id)
            ->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Riwayat Transakti Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDataDetails = [];
        if ($getData->type_service == 1) {
            $getDataDetails = $getData->getTransactionDetails()->selectRaw('transaction_details.id,
                transaction_details.product_id, transaction_details.product_name, transaction_details.product_qty,
                transaction_details.product_price,
                product.image, CONCAT("'.env('OSS_URL').'/'.'", product.image) AS image_full, CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                ->join('product', 'product.id', '=', 'transaction_details.product_id', 'LEFT')
                ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                ->get();
        }
        else if ($getData->type_service == 2) {
            $getDataDetails = $getData->getTransactionDetails()->selectRaw('transaction_details.*,
                doctor_category.name AS doctor_category_name, users.image, date_available, time_start, time_end, klinik.name as klinik_name,
                CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                ->join('doctor', 'doctor.id', '=', 'transaction_details.doctor_id', 'LEFT')
                ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id', 'LEFT')
                ->join('users', 'users.id', '=', 'doctor.user_id', 'LEFT')
                ->join('doctor_schedule','doctor_schedule.id','=','transaction_details.schedule_id','LEFT')
                ->join('klinik', 'klinik.id', '=', 'users.klinik_id')
                ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                ->first();
        }
        else if ($getData->type_service == 3) {
            $getDataDetails = $getData->getTransactionDetails()->selectRaw('transaction_details.*,
                lab.image, date_available, time_start, time_end, CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, CONCAT("'.env('OSS_URL').'/'.'", payment.icon_img) AS payment_icon')
                ->join('lab', 'lab.id', '=', 'transaction_details.lab_id', 'LEFT')
                ->join('lab_schedule','lab_schedule.id','=','transaction_details.schedule_id', 'LEFT')
                ->join('transaction','transaction.id','=','transaction_details.transaction_id', 'LEFT')
                ->join('payment', 'payment.id', '=', 'transaction.payment_id')
                ->get();
        }
        else if ($getData->type_service == 4) {
            $getDataDetails = $getData->getTransactionDetails()->selectRaw('transaction_details.*')->get();
        }

        $paymentInfo = json_decode($getData->payment_info, TRUE);
        $userAddress = [
            'receiver_name' => $getData->receiver_name,
            'receiver_address' => $getData->receiver_address,
            'receiver_phone' => $getData->receiver_phone,
            'shipping_address_name' => $getData->shipping_address_name,
            'shipping_address' => $getData->shipping_address,
            'shipping_city_id' => $getData->shipping_city_id,
            'shipping_city_name' => $getData->shipping_city_name,
            'shipping_district_id' => $getData->shipping_district_id,
            'shipping_district_name' => $getData->shipping_district_name,
            'shipping_subdistrict_id' => $getData->shipping_subdistrict_id,
            'shipping_subdistrict_name' => $getData->shipping_subdistrict_name,
            'shipping_zipcode' => $getData->shipping_zipcode
        ];

        unset($getData->payment_info);
        unset($getData->extra_info);

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $getData,
                'details' => $getDataDetails,
                'payment_info' => $paymentInfo,
                'user_address' => $userAddress,
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

//        $userId = $user->id;
//        $getDataId = $getData->id;
//
//        if ($getData->type_service == 1) {
//            $getDataProduct = $this->getDetailProduct($getDataId, $userId);
//
//            $listProduct = Product::selectRaw('transaction_details.id, product.id as product_id, product_qty, product.name, product.image, product.price')
//                ->join('transaction_details', 'transaction_details.product_id', '=', 'product.id')
//                ->where('transaction_details.transaction_id', $getDataProduct->id)->get();
//
//            $historyProduct = [
//                'transaction_product' => $getDataProduct,
//                'list_product' => $listProduct,
//                'address' => $this->getUserAddress($user->id)
//            ];
//
//            return response()->json([
//                'success' => 1,
//                'data' => $historyProduct,
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ]);
//
//        } else if (in_array($getData->type_service, [2, 3, 4])) {
//            $getDataDoctor = $this->getDetailDoctor($getDataId, $userId);
//
//            return response()->json([
//                'success' => 1,
//                'data' => $getDataDoctor,
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ]);
//
//        } else if (in_array($getData->type_service, [5, 6, 7])) {
//
//            $getDataLab = $this->getDetailLab($getDataId, $userId);
//
//            if ($getDataLab) {
//                return response()->json([
//                    'success' => 1,
//                    'data' => $getDataLab,
//                    'token' => $this->request->attributes->get('_refresh_token'),
//                ]);
//            }
//        }
//        else {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Tipe Riwayat Transakti Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }

    }

    private function getListProduct($userId, $getLimit, $s)
    {
        $result = Transaction::selectRaw('transaction.id, transaction.created_at,
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
            ->where('transaction.user_id', $userId)
            ->whereIn('type_service', [1,5])->orderBy('transaction.id','DESC');

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('product_name', 'LIKE', "%$s%");
        }

        $getData = $result->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name, transaction.total_qty, transaction.subtotal, transaction.total,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($getLimit);

        return [
            'data' => $getData,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    private function getListDoctor($userId, $getServiceId, $getLimit, $s)
    {
        $getServiceDoctor = isset($this->setting['service-doctor']) ? json_decode($this->setting['service-doctor'], true) : [];
        $getService = Service::whereIn('id', $getServiceDoctor)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();

        $tempService = [
            [
                'id' => 0,
                'name' => 'Semua',
                'type' => 0,
                'type_nice' => '',
                'active' => 0
            ]
        ];
        foreach ($getService as $list) {
            $temp = [
                'id' => $list->id,
                'name' => $list->name,
                'type' => $list->type,
                'type_nice' => $list->type_nice,
                'active' => 0
            ];

            if ($list->id == $getServiceId) {
                $temp['active'] = 1;
            }
            $tempService[] = $temp;
        }

        $getService = $tempService;

        $result = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            doctor_category.name as category_doctor, MIN(doctor_name) AS doctor_name,
            MIN(users.image) AS image, CONCAT("'.env('OSS_URL').'/'.'", MIN(users.image)) AS image_full')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id','=','transaction.id')
            ->leftJoin('doctor', 'doctor.id','=','transaction_details.doctor_id')
            ->leftJoin('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->leftJoin('users', 'users.id','=','doctor.user_id')
            ->where('transaction.user_id', $userId)
            ->where('type_service', 2);

        if ($getServiceId > 0) {
            $result = $result->where('category_service_id', $getServiceId);
        }

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('doctor_name', 'LIKE', "%$s%");
        }

        $getData = $result->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            category_doctor')
            ->paginate($getLimit);

        return [
            'data' => $getData,
            'service' => $getService,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    private function getListLab($userId, $getServiceId, $getLimit, $s)
    {
        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];
        $getService = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();

        $tempService = [
            [
                'id' => 0,
                'name' => 'Semua',
                'type' => 0,
                'type_nice' => '',
                'active' => 0
            ]
        ];
        foreach ($getService as $list) {
            $temp = [
                'id' => $list->id,
                'name' => $list->name,
                'type' => $list->type,
                'type_nice' => $list->type_nice,
                'active' => 0
            ];

            if ($list->id == $getServiceId) {
                $temp['active'] = 1;
            }
            $tempService[] = $temp;
        }

        $getService = $tempService;

        $result = Transaction::selectRaw('transaction.id, transaction.created_at,
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
            ->where('transaction.user_id', $userId)
            ->where('type_service', 3)
            ->orderBy('transaction.id','DESC');

        if ($getServiceId > 0) {
            $result = $result->where('category_service_id', $getServiceId);
        }

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('lab_name', 'LIKE', "%$s%");
        }

        $getData = $result->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($getLimit);

        return [
            'data' => $getData,
            'service' => $getService,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    private function getListNurse($userId, $getLimit)
    {
        $result = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status, nurse_booked, nurse_shift as shift_qty')
            ->join('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('transaction.user_id', $userId)
            ->where('type_service', 4)
            ->orderBy('transaction.id','DESC');

        $getData = $result->groupByRaw('transaction.id, transaction.created_at,
                    transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
                    nurse_booked , shift_qty')
                    ->paginate($getLimit);

        return [
            'data' => $getData,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }


    private function getDetailProduct($getDataId, $userId)
    {
        $getData = Transaction::selectRaw('transaction.id, transaction.code, transaction.receiver_address, transaction.receiver_phone, transaction.created_at, shipping_address_name, shipping_name, shipping_price,  transaction.total as total, transaction.status as status, transaction.type_service, payment.icon_img as payment_image, transaction.payment_info as payment_info')
                    ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
                    ->join('payment','payment.id', '=','transaction.payment_id')
                    ->where('transaction.user_id', $userId)
                    ->where('transaction.id', $getDataId)
                    ->where('transaction.type', 1)->first();

        if (strlen($getData['payment_image']) > 0) {
            $getData['payment_image_full'] = env('OSS_URL').'/'.$getData['payment_image'];
        }
        else {
            $getData['payment_image_full'] = asset('assets/cms/images/no-img.png');
        }

        return $getData;
    }

    private function getDetailLab($getDataId, $userId)
    {

        $getData = Transaction::selectRaw('transaction.id, code, transaction.type_service, time_start, time_end, date_available, transaction.total as total_price,
        transaction.status as status, payment_name, payment.icon_img as payment_image, transaction.payment_info as payment_info')
       ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
       ->join('lab_schedule','lab_schedule.id','=','transaction_details.schedule_id')
       ->join('payment','payment.id', '=','transaction.payment_id')
       ->where('transaction.user_id', $userId)
       ->where('transaction.id', $getDataId)
       ->first();

        $getResult = [];

        if (strlen($getData['payment_image']) > 0) {
            $getData['payment_image_full'] = env('OSS_URL').'/'.$getData['payment_image'];
        }
        else {
            $getData['payment_image_full'] = asset('assets/cms/images/no-img.png');
        }
        $paymentInfo = json_decode($getData['payment_info'], true);

        unset($getData['payment_info']);

        $getResult[] = $getData;

        return [
            'data' => $getResult,
            'infoPayment' => $paymentInfo
        ];
    }

    private function getDetailDoctor($getDataId, $userId)
    {
        $paymentInfo = [];
        $getData = Transaction::selectRaw('transaction.id, code, transaction_details.doctor_name as doctor_name,
            transaction.created_at, transaction.type_service, doctor_category.name as category, klinik.name as clinic_name,
            transaction.total as total_price, transaction.status as status, users.image as image, payment_name,
            payment.icon_img as payment_icon, transaction.payment_info as payment_info')
            ->join('klinik', 'klinik.id', '=', 'transaction.klinik_id')
            ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
            ->join('doctor', 'doctor.id', '=', 'transaction_details.doctor_id')
            ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('payment', 'payment.id', '=', 'transaction.payment_id')
            ->where('transaction.user_id', $userId)
            ->where('transaction.id', $getDataId)
            ->first();

        if ($getData) {
            if (strlen($getData['image']) > 0) {
                $getData['image_full'] = env('OSS_URL') . '/' . $getData['image'];
            } else {
                $getData['image_full'] = asset('assets/cms/images/no-img.png');
            }
            if (strlen($getData['payment_image']) > 0) {
                $getData['payment_image_full'] = env('OSS_URL') . '/' . $getData['payment_image'];
            } else {
                $getData['payment_image_full'] = asset('assets/cms/images/no-img.png');
            }

            $paymentInfo = json_decode($getData['payment_info'], true);

            unset($getData['payment_info']);

        }

        return [
            'data' => $getData,
            'info_payment' => $paymentInfo,
            'user_address'
        ];

    }

    private function getUserAddress($userId)
    {
        $getUsersAddress = UsersAddress::where('user_id', $userId)->first();
        $user = $this->request->attributes->get('_user');

        $getAddressName = $getUsersAddress->address_name ??$user->address ?? '';
        $getAddress = $getUsersAddress->address ?? $user->address_detail ?? '';
        $getCity = $getUsersAddress->city_id ?? '';
        $getCityName = $getUsersAddress->city_name ?? '';
        $getDistrict = $getUsersAddress->district_id ?? '';
        $getDistrictName = $getUsersAddress->district_name ?? '';
        $getSubDistrict = $getUsersAddress->sub_district_id ?? '';
        $getSubDistrictName = $getUsersAddress->sub_district_name ?? '';
        $getZipCode = $getUsersAddress->zip_code ?? '';
        $getPhone = $user->phone ?? '';

        return [
            'address_name' => $getAddressName,
            'address' => $getAddress,
            'city_id' => $getCity,
            'city_name' => $getCityName,
            'district_id' => $getDistrict,
            'district_name' => $getDistrictName,
            'sub_district_id' => $getSubDistrict,
            'sub_district_name' => $getSubDistrictName,
            'zip_code' => $getZipCode,
            'phone' => $getPhone
        ];

    }

}
