<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        $s = strip_tags($this->request->get('s'));
        $getLimit = intval($this->request->get('limit'));

        if ($getDoctor == 1) {
            $getData = $this->getListDoctor($user->id, $getServiceId, $getLimit, $s);
            $getProduct = 0;
            $getLab = 0;
        }
        else if ($getLab == 1) {
            $getData = $this->getListLab($user->id, $getServiceId, $getLimit, $s);
            $getProduct = 0;
            $getDoctor = 0;
        }
        elseif ($getProduct == 1) {
            $getData = $this->getListProduct($user->id, $getLimit, $s);
            $getDoctor = 0;
            $getLab = 0;
        }
        else {
            $getData = $this->getListProduct($user->id, $getLimit, $s);
            $getProduct = 1;
            $getDoctor = 0;
            $getLab = 0;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $getData['data'],
                'service' => $getData['service'] ?? [],
                'active' => [
                    'doctor' => $getDoctor,
                    'product' => $getProduct,
                    'lab' => $getLab,
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }


    public function detail($id){
        $user = $this->request->attributes->get('_user');

        $getData = Transaction::where('id',$id)->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Riwayat Transakti Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ],404);
          }

        $userId = $user->id;
        $getDataId = $getData->id;

        $getDataDoctor =  $this->getDetailDoctor($getDataId, $userId);

        if ($getDataDoctor) {
            return response()->json([
                'success' => 0,
                'data' => $getDataDoctor,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
          }
       
        $getDataLab =  $this->getDetailLab($getDataId, $userId);
      
        if($getDataLab) {
         return response()->json([
             'success' => 0,
             'data' => $getDataLab,
             'token' => $this->request->attributes->get('_refresh_token'),
         ]);
        }

       $getDataProduct = $this->getDetailProduct($getDataId, $userId);
       
   
       $listProduct = Product::selectRaw('transaction_details.id, product.id as product_id, product_qty, product.name, product.image, product.price')
       ->join('transaction_details', 'transaction_details.product_id', '=', 'product.id')
       ->where('transaction_details.transaction_id', $getDataProduct->id)->get();

        
      $historyProduct = [
          'transaction_product' => $getDataProduct,
          'list_product' => $listProduct,
          'address' =>  $this->getUserAddress($user->id)
      ];

     if($getDataProduct){
        return response()->json([
            'success' => 0,
            'data' => $historyProduct,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
      }else{
        return response()->json([
            'success' => 0,
            'message' => ['Riwayat Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);
      }

    }

    private function getListProduct($userId, $getLimit, $s)
    {
        $getType = check_list_type_transaction('product');

        $result = Transaction::selectRaw('transaction.id, transaction.status, total, subtotal, type, MIN(product_name) AS product_name, MIN(product.image) as image')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                     ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('product', function($join){
                $join->on('product.id','=','transaction_details.product_id')
                     ->on('product.id', '=', DB::raw("(select min(id) from product WHERE product.id = transaction_details.product_id)"));
            })
            ->where('transaction.user_id', $userId)
            ->where('type', $getType)->orderBy('transaction.id','DESC');

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('product_name', 'LIKE', "%$s%");
        }

        $getData = $result->groupByRaw('transaction.id, transaction.status, total, subtotal, type, product_name, image')->paginate($getLimit);
        $getResult = [];
        foreach ($getData as $list) {
            $getTemp = $list->toArray();
            if (strlen($getTemp['image']) > 0) {
                $getTemp['image_full'] = env('OSS_URL').'/'.$getTemp['image'];
            }
            else {
                $getTemp['image_full'] = asset('assets/cms/images/no-img.png');
            }

            $getResult[] = $getTemp;
        }

        return [
            'data' => $getResult
        ];

    }

    private function getListDoctor($userId, $getServiceId, $getLimit, $s)
    {
        $getServiceDoctor = isset($this->setting['service-doctor']) ? json_decode($this->setting['service-doctor'], true) : [];
        $getService = Service::whereIn('id', $getServiceDoctor)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();

        $notFound = 1;
        $firstService = 0;
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
                $notFound = 0;
                $temp['active'] = 1;
            }
            $tempService[] = $temp;
        }

        $getService = $tempService;

        if ($notFound == 1) {
            if (count($getService) > 0) {
                $getService[0]['active'] = 1;
            }
            $getServiceId = $firstService;
        }

        $getType = check_list_type_transaction('doctor', $getServiceId);

        $result = Transaction::selectRaw('transaction.id, transaction.created_at, transaction.type, doctor_category.name as category_doctor, transaction.status, MIN(doctor_name) AS doctor_name, MIN(users.image) AS image')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id','=','transaction.id')
            ->leftJoin('doctor', 'doctor.id','=','transaction_details.doctor_id')
            ->leftJoin('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->leftJoin('users', 'users.id','=','doctor.user_id')
            ->where('transaction.user_id', $userId)->orderBy('transaction.id','DESC');

        
        if ($getServiceId <= 0) {
            $result = $result->whereIn('type', [2,3,4]);
        }
        else {
            $result = $result->where('type', $getType);
        }

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('doctor_name', 'LIKE', "%$s%");
        }

        $getData = $result->groupByRaw('transaction.id, transaction.type, transaction.created_at, transaction.status, category_doctor, doctor_name, image')->paginate($getLimit);
        $getResult = [];
        foreach ($getData as $list) {
            $getTemp = $list->toArray();
            if (strlen($getTemp['image']) > 0) {
                $getTemp['image_full'] = env('OSS_URL').'/'.$getTemp['image'];
            }
            else {
                $getTemp['image_full'] = asset('assets/cms/images/no-img.png');
            }

            $getResult[] = $getTemp;
        }

        return [
            'data' => $getResult,
            'service' => $getService
        ];

    }

    private function getListLab($userId, $getServiceId, $getLimit, $s)
    {
        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];
        $getService = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();

        $notFound = 1;
        $firstService = 0;
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
                $notFound = 0;
                $temp['active'] = 1;
            }
            $tempService[] = $temp;
        }

        $getService = $tempService;

        if ($notFound == 1) {
            if (count($getService) > 0) {
                $getService[0]['active'] = 1;
            }
            $getServiceId = $firstService;
        }

        $getType = check_list_type_transaction('lab', $getServiceId);

        $result = Transaction::selectRaw('transaction.id, transaction.created_at, transaction.status, type, MIN(lab_name) AS lab_name, MIN(lab.image) as image')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                     ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('lab', function($join){
                $join->on('lab.id','=','transaction_details.lab_id')
                     ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = transaction_details.lab_id)"));
            })
            ->where('transaction.user_id', $userId)->orderBy('transaction.id','DESC');

        if ($getServiceId <= 0) {
            $result = $result->whereIn('type', [5,6,7]);
        }
        else {
            $result = $result->where('type', $getType);
        }

        if (strlen($s) > 0) {
            $result = $result->where('code', 'LIKE', "%$s%")->orWhere('lab_name', 'LIKE', "%$s%");
        }

        $getData = $result->groupByRaw('transaction.id, transaction.created_at, transaction.status, type, lab_name, image')->paginate($getLimit)->get();
        $getResult = [];
        foreach ($getData as $list) {
            $getTemp = $list->toArray();
            if (strlen($getTemp['image']) > 0) {
                $getTemp['image_full'] = env('OSS_URL').'/'.$getTemp['image'];
            }
            else {
                $getTemp['image_full'] = asset('assets/cms/images/no-img.png');
            }

            $getResult[] = $getTemp;
        }

        return [
            'data' => $getResult,
            'service' => $getService
        ];

    }

    private function getDetailProduct($getDataId, $userId){
        return 
        Transaction::selectRaw('transaction.id, transaction.code, transaction.receiver_address, transaction.receiver_phone, transaction.created_at, shipping_address_name, shipping_name, shipping_price,  transaction.total as total, transaction.status as status, transaction.type')
        ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
        ->where('transaction.user_id', $userId)
        ->where('transaction.id', $getDataId)
        ->where('type', 1)->first();
    }
    private function getDetailLab($getDataId, $userId){
        return 
        Transaction::selectRaw('transaction.id, code, time_start, time_end, date_available, transaction.total as total_price,
        transaction.status as status, payment_name, payment.icon_img as icon')
       ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
       ->join('lab_schedule','lab_schedule.id','=','transaction_details.schedule_id')
       ->join('payment','payment.id', '=','transaction.payment_id')
       ->where('transaction.user_id', $userId)
       ->where('transaction.id', $getDataId)
       ->first();
    }
    private function getDetailDoctor($getDataId, $userId){
       return 
         Transaction::selectRaw('transaction.id, code, transaction_details.doctor_name as doctor_name, transaction.created_at, transaction.type, doctor_category.name as category, klinik.name as clinic_name, transaction.total as total_price,
         transaction.status as status, users.image as image, payment_name, payment.icon_img as icon')
        ->join('klinik','klinik.id', '=', 'transaction.klinik_id')
        ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
        ->join('doctor', 'doctor.id','=','transaction_details.doctor_id')
        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
        ->join('users','users.id','=','doctor.user_id')
        ->join('payment','payment.id', '=','transaction.payment_id')
        ->where('transaction.user_id', $userId)
        ->where('transaction.id', $getDataId)
        ->first();
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
