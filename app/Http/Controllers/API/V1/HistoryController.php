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
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 10;
    }

    public function index(){
        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));
        $doctor = intval($this->request->get('doctor'));
        $lab = intval($this->request->get('lab'));
        $product = intval($this->request->get('product'));

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $service = Service::orderBy('orders', 'ASC')->get();
        //$category = DoctorCategory::orderBy('orders', 'ASC')->get();

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $tempService = [];
        $firstService = 0;
        $getService = 0;
        $getServiceDataTemp1 = false;
        $getServiceData = false;
        foreach ($service as $index => $list) {
            $temp = [
                'id' => $list->id,
                'name' => $list->name,
                'active' => 0
            ];

            if ($index == 0) {
                $firstService = $list->id;
                $getServiceDataTemp1 = $list;
            }

            if ($list->id == $getInterestService) {
                $temp['active'] = 1;
                $getService = $list->id;
                $getServiceData = $list;
            }

            $tempService[] = $temp;
        }

//        $service = $tempService;
//        if ($getService == 0) {
//            if ($firstService > 0) {
//                if($doctor == 1) {
//                    $service[0]['active'] = 1;
//                    $service[2]['active'] = 1;
//                    $service[4]['active'] = 1;
//                }
//                else if($lab == 1) {
//                    $service[1]['active'] = 1;
//                    $service[3]['active'] = 1;
//                    $service[5]['active'] = 1;
//                }
//                else {
//                    $service[0]['active'] = 1;
//                }
//            }
//            $getServiceData = $getServiceDataTemp1;
//        }

        $service = $tempService;
        if ($getService == 0) {
            if ($firstService > 0) {
                $service[0]['active'] = 1;
            }
            $getService = $firstService;
            $getServiceData = $getServiceDataTemp1;
        }

        if($doctor == 1){
            switch ($getService) {
//                case 0 : $getType = [2,3,4]; break;
                case 2 : $getType = [3]; break;
                case 3 : $getType = [4]; break;
                default : $getType = [2]; break;
            }

            $getDataDoctor = Transaction::selectRaw('transaction.id, transaction.code, transaction.created_at, transaction_details.doctor_name as doctor_name, transaction_details.doctor_price as doctor_price, transaction.status as status, type, users.image as image, doctor_category.name as category_name')
            ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
            ->join('doctor', 'doctor.id','=','transaction_details.doctor_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->join('users','users.id','=','doctor.user_id')
            ->where('transaction.user_id', $user->id)
            ->whereIn('type', $getType)
            ->get();

            if(!$getDataDoctor){
                return response()->json([
                    'success' => 0,
                    'message' => ['Riwayat Transaksi Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            return response()->json([
                'success' => 1,
                'data' => [
                    'service' => $service,
                    'data' => $getDataDoctor,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        elseif($lab == 1){
            switch ($getService) {
//                case 0 : $getType = [5,6,7]; break;
                case 2 : $getType = [6]; break;
                case 3 : $getType = [7]; break;
                default : $getType = [5]; break;
            }

            $getDataLab = Transaction::selectRaw('transaction.id, transaction.code, transaction.created_at, transaction_details.lab_name as lab_name, transaction_details.lab_price as lab_price, status, type, lab.image as image')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                     ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('lab', function($join){
                $join->on('lab.id','=','transaction_details.lab_id')
                     ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = transaction_details.lab_id)"));
            })
            ->where('transaction.user_id', $user->id)
            ->whereIn('type', $getType)
            ->get();

            if(!$getDataLab){
                return response()->json([
                    'success' => 0,
                    'message' => ['Riwayat Transaksi Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            return response()->json([
                'success' => 1,
                'data' => [
                    'service' => $service,
                    'data' => $getDataLab,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }else{

            $getDataProduct = Transaction::selectRaw('transaction.id, transaction.code,transaction.created_at, transaction_details.product_name as product_name, transaction.total as price, transaction.status as status, type, product.image as image')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                     ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('product', function($join){
                $join->on('product.id','=','transaction_details.product_id')
                     ->on('product.id', '=', DB::raw("(select min(id) from product WHERE product.id = transaction_details.product_id)"));
            })
            ->where('transaction.user_id', $user->id)
            ->where('type', 1)
            ->get();

            if(!$getDataProduct){
                return response()->json([
                    'success' => 0,
                    'message' => ['Riwayat Transaksi Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            return response()->json([
                'success' => 1,
                'data' => [
                    'data' => $getDataProduct,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }


    }
    Public function detail($id){
        $user = $this->request->attributes->get('_user');

        $getData = Transaction::where('id',$id)->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Riwayat Transakti Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ],404);
          }

        //dd($getData->id);
        $getDataDoctor = Transaction::selectRaw('transaction.id, code, transaction_details.doctor_name as doctor_name, doctor_category.name as category, klinik.name as clinic_name, transaction.total as total_price,
         transaction.status as status, users.image as image, payment_name, payment.icon_img as icon')
        ->join('klinik','klinik.id', '=', 'transaction.klinik_id')
        ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
        ->join('doctor', 'doctor.id','=','transaction_details.doctor_id')
        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
        ->join('users','users.id','=','doctor.user_id')
        ->join('payment','payment.id', '=','transaction.payment_id')
        ->where('transaction.user_id', $user->id)
        ->where('transaction.id', $id)
        ->first();

        $getDataLab = Transaction::selectRaw('transaction.id, code, time_start, time_end, date_available, transaction.total as total_price,
        transaction.status as status, payment_name, payment.icon_img as icon')
       ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
       ->join('lab_schedule','lab_schedule.id','=','transaction_details.schedule_id')
       ->join('payment','payment.id', '=','transaction.payment_id')
       ->where('transaction.user_id', $user->id)
       ->where('transaction.id', $id)
       ->first();
       if ($getDataDoctor) {
        return response()->json([
            'success' => 0,
            'data' => $getDataDoctor,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
      }
       $getDataProduct = Transaction::selectRaw('transaction.id, shipping_address_name, shipping_name, shipping_price,  transaction.total as total, transaction.status as status, transaction.type')
       ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
       ->where('transaction.user_id', $user->id)
       ->where('transaction.id', $id)
       ->where('type', 1)
       ->first();
       if($getDataLab) {
        return response()->json([
            'success' => 0,
            'data' => $getDataLab,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

       $listProduct = Product::selectRaw('transaction_details.id, product.id as product_id, product.name, product.image, product.price')
       ->join('transaction_details', 'transaction_details.product_id', '=', 'product.id')
       ->where('transaction_details.transaction_id', $getDataProduct->id)->get();


      $historyProduct = [
          'Transaction Product' => $getDataProduct,
          'list Product' => $listProduct
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
}
