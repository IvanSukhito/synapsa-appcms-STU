<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\TransactionHistoryLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Payment;
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
        $getSubServiceId = intval($this->request->get('sub_service_id'));
        $getDoctor = intval($this->request->get('doctor'));
        $getLab = intval($this->request->get('lab'));
        $getNurse = intval($this->request->get('nurse'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = intval($this->request->get('limit'));

        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $transactionHistoryLogic = new TransactionHistoryLogic();

        if ($getDoctor == 1) {
            $getData = $transactionHistoryLogic->doctorHistory($user->id, $getServiceId, $getSubServiceId, $getLimit, $s);
            $getProduct = 0;
            $getLab = 0;
            $getNurse = 0;
        }
        elseif ($getLab == 1) {
            $getData = $transactionHistoryLogic->labHistory($user->id, $getServiceId, $getSubServiceId, $getLimit, $s);
            $getProduct = 0;
            $getDoctor = 0;
            $getNurse = 0;
        }
        elseif ($getNurse == 1){
            $getData = $this->getListNurse($user->id, $getLimit);
            $getDoctor = 0;
            $getLab = 0;
            $getProduct = 0;
        }
        else {
            $getData = $transactionHistoryLogic->productHistory($user->id, $getLimit, $s);
            $getProduct = 1;
            $getDoctor = 0;
            $getLab = 0;
            $getNurse = 0;
        }

        $active = $getData['active'] ?? [];
        $active['doctor'] = $getDoctor;
        $active['product'] = $getProduct;
        $active['lab'] = $getLab;
        $active['nurse'] = $getNurse;

        $getService = $getData['service'] ?? [];
        $getSubService = $getData['sub_service'] ?? [];
        if ($getService) {
            $temp = [[
                'id' => 0,
                'name' => 'Semua',
                'type' => 0,
                'type_nice' => '',
                'active' => $getServiceId == 0 ? 1 : 0
            ]];
            foreach ($getService as $list) {
                $temp[] = $list;
            }
            $getService = $temp;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $getData['data'],
                'service' => $getService,
                'sub_service' => $getSubService,
                'default_image' => $getData['default_image'] ?? '',
                'active' => $active
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $transactionHistoryLogic = new TransactionHistoryLogic();
        $getData = $transactionHistoryLogic->detailHistory($user->id, $id);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Tipe Riwayat Transakti Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function rePayment($id)
    {
        $user = $this->request->attributes->get('_user');

        $transactionHistoryLogic = new TransactionHistoryLogic();
        $getData = $transactionHistoryLogic->detailHistory($user->id, $id);

        $getData = Transaction::where('id', '=', $id)
            ->where('user_id', '=', $user->id)
            ->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Riwayat Transakti Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        if ($getData->status == 2) {

            $getCode = str_pad(($getData->id), 6, '0', STR_PAD_LEFT).rand(100000,999999);

            $getAdditional = json_decode($getData->send_info, true);
            $getAdditional['code'] = $getCode;
            $getAdditional['job']['code'] = $getCode;

            $newLogic = new SynapsaLogic();
            $getPayment = Payment::where('id', $getData->payment_id)->first();
            $getResult = $newLogic->createPayment($getPayment, $getAdditional, $id);
            if ($getResult['success'] == 1) {

                $getInfo = isset($getResult['info']) ? $getResult['info'] : '';

                return response()->json([
                    'success' => 1,
                    'data' => [
                        'payment' => 0,
                        'info' => $getInfo,
                    ],
                    'message' => ['Berhasil'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ]);

            }


        }

        return response()->json([
            'success' => 0,
            'message' => ['Riwayat Transakti Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

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
        $user = $this->request->attributes->get('_user');

        $logic = new SynapsaLogic();
        return $logic->getUserAddress($user->id, $user->phone);

    }

}
