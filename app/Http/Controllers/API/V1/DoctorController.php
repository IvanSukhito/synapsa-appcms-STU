<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\TempAd;
use App\Codes\Models\V1\TempAdDetail;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
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
        $this->limit = 5;
    }

    public function getDoctor(){
        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Doctor::selectRaw('users.id, users.fullname as doctor_name, users.image as image ,doctor.price as doctor_price, doctor_category.name as category')
        ->join('users', 'users.id', '=', 'doctor.user_id')
        ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')->where('users.doctor','=', 1);

       
        if (strlen($s) > 0) {
            $data = $data->where('users.fullname', 'LIKE', strip_tags($s))->orWhere('doctor_category.name', 'LIKE', strip_tags($s));
        }
        $data = $data->orderBy('id','DESC')->paginate($getLimit);
        $category = DoctorCategory::get();

        return response()->json([
            'success' => 1,
            'data' => [
                'doctor' => $data,
                'category' => $category
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getDoctorDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Doctor::selectRaw('users.id, users.fullname as doctor_name, users.dob as dob, users.gender as gender, users.phone as phone, users.address as address, users.image as image ,doctor.price as doctor_price, doctor.formal_edu as doctor_edu, doctor.nonformal_edu as doctor_nonformal_edu, doctor_category.name as category')
        ->join('users', 'users.id', '=', 'doctor.user_id')
        ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')->where('users.doctor','=', 1)->where('doctor.id', $id)->first();

     
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => 'Doctor Not Found',
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $data,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function bookDoctor($id){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'book_day' => 'required',
            'book_at' => 'required',

        ]);

        $bookAt = $this->request->get('book_at');
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        
        $doctorSchedule = DoctorSchedule::where('time_start', '<=',$bookAt)->where('time_end', '>=',$bookAt)->where('doctor_id', $id)->first();
        //dd($doctorSchedule);  

        if(!$doctorSchedule){

            return response()->json([
                'success' => 0,
                'message' => 'Tidak Ada Jadwal Dokter',
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else{
          
            $janjiTemu = new AppointmentDoctor();
            $janjiTemu->doctor_id = $id;
            $janjiTemu->book_day = $this->request->get('book_day');
            $janjiTemu->book_at = $this->request->get('book_at');
            $janjiTemu->save();

            if($janjiTemu){
                try{
                    $TempAd = TempAd::firstOrCreate([
                        'users_id' => $user->id,
                    ]);
                    $cart = TempAdDetail::firstOrCreate([
                        'temp_ad_id' => $TempAd->id,
                        'appointment_doctor_id' => $janjiTemu->id,
                        'doctor_id' => $id
                    ]);                  
                }
        
                catch (QueryException $e){
                    return response()->json([
                        'message' => 'Insert Failed'
                    ], 500);
                }
            }
            return response()->json([
                'success' => 1,
                'data' => $janjiTemu,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
    }

    public function tes(){

    }
    public function getAddress(){
        $user = $this->request->attributes->get('_user');

        $getData = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getData = json_decode($getData->detail_address, true);
        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getData['address_name'] ?? $getUsersAddress->address_name;
        $getAddress = $getData['address'] ?? $getUsersAddress->address;
        $getCity = $getData['city_id'] ?? $getUsersAddress->city_id;
        $getDistrict = $getData['district_id'] ?? $getUsersAddress->district_id;
        $getSubDistrict = $getData['sub_district_id'] ?? $getUsersAddress->sub_district_id;
        $getZipCode = $getData['zip_code'] ?? $getUsersAddress->zip_code;

        return response()->json([
            'success' => 1,
            'data' => [
                'address_name' => $getAddressName,
                'address' => $getAddress,
                'city_id' => $getCity,
                'district_id' => $getDistrict,
                'sub_district_id' => $getSubDistrict,
                'zip_code' => $getZipCode,
            ]
        ]);
    }

    public function updateAddress(){
        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'address_name' => 'required',
            'address' => 'required',
            'city_id' => '',
            'district_id' => '',
            'sub_district_id' => '',
            'zip_code' => ''
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getAddressName = $this->request->get('address_name');
        $getAddress = $this->request->get('address');
        $getCity = $this->request->get('city_id');
        $getDistrict = $this->request->get('district_id');
        $getSubDistrict = $this->request->get('sub_district_id');
        $getZipCode = $this->request->get('zip_code');

        $getUsersCart = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDetailAddress = [
            'address_name' => $getAddressName,
            'address' => $getAddress,
            'city_id' => $getCity,
            'district_id' => $getDistrict,
            'sub_district_id' => $getSubDistrict,
            'zip_code' => $getZipCode,
        ];
        $getUsersCart->detail_address = json_encode($getDetailAddress);
        $getUsersCart->save();

        return response()->json([
            'success' => 1,
            'message' => 'Detail Address Has Been Updated',
            'data' => $getDetailAddress
        ]);
    }

    public function getReceiver(){
        $user = $this->request->attributes->get('_user');

        $getData = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getData = json_decode($getData->detail_information, true);
        if ($getData) {
       
            $getAddress = $getData['address'] ?? '';
            $getPhone = $getData['phone'] ?? '';
        }
        else {
       
            $getAddress = $user->address ?? '';
            $getPhone = $user->phone ?? '';
        }

        return response()->json([
            'success' => 1,
            'data' => [
                [
                  
                    'address' => $getAddress ?? '',
                    'phone' => $getPhone ?? '',
                ]
            ]
        ]);
    }

    
    public function updateReceiver(){
        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
         
            'address' => 'required',
            'phone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

      
        $address = $this->request->get('address');
        $phone = $this->request->get('phone');

        $getUsersCart = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getInformation = [
            'address' => $address,
            'phone' => $phone,
        ];
        $getUsersCart->detail_information = json_encode($getInformation);
        $getUsersCart->save();

        $getData = ['detail_information' => $getInformation];


        return response()->json([
            'success' => 1,
            'message' => 'Detail Information Has Been Updated',
            'data' => $getData
        ]);
    }


    public function getPayment()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersCart = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCartDetails = Doctor::selectRaw('temp_ad_detail.id, users.fullname AS doctor_name,
        users.image as image ,doctor.price as doctor_price, doctor_category.name as category')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('temp_ad_detail', 'temp_ad_detail.doctor_id', '=', 'doctor.id')
            ->where('temp_ad_detail.temp_ad_id', '=', $getUsersCart->id)->get();
            

       
        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
          
            $subTotal += $list->doctor_price;
        }

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'subtotal' => $subTotal,
                    'total' => $subTotal
                ],
                'cart_details' => $getUsersCartDetails,
                'payment' => $getPayment
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }
    
    public function cartSummary()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersCart = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

      
        $getUsersCartDetails = Doctor::selectRaw('temp_ad_detail.id, users.fullname AS doctor_name,
        users.image as image ,doctor.price as doctor_price, doctor_category.name as category')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('temp_ad_detail', 'temp_ad_detail.doctor_id', '=', 'doctor.id')
            ->where('temp_ad_detail.temp_ad_id', '=', $getUsersCart->id)->get();
            
        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
            $subTotal +=$list->doctor_price;
        }

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
     
        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'subtotal' => $subTotal,
                    'total' => $subTotal 
                ],
                'cart_details' => $getUsersCartDetails
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric',
        ]);

        $paymentId = $this->request->get('payment_id');
        $getPayment = Payment::where('id', $paymentId)->first();
        $paymentInfo = [];

        $getUsersCart = TempAd::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDetailAddress = json_decode($getUsersCart->detail_address, true);
        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
      

        $getUsersCartDetails = Doctor::selectRaw('temp_ad_detail.id, users.fullname AS doctor_name,
        users.image as image ,doctor.price as doctor_price, doctor_category.name as category')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('temp_ad_detail', 'temp_ad_detail.doctor_id', '=', 'doctor.id')
            ->where('temp_ad_detail.temp_ad_id', '=', $getUsersCart->id)->get();
            

        $totalQty = 0;
        $subTotal = 0;
     
        foreach ($getUsersCartDetails as $list) {
        
            $subTotal += $list->doctor_price;
          
        }
        $total = $subTotal;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $user->klinik_id,
            'user_id' => $user->id,
            'code' => $newCode,
            'payment_id' => $paymentId,
            'payment_name' => $getPayment->name,
            'receiver_address' => $getDetailsInformation['address'] ?? '',
            'receiver_phone' => $getDetailsInformation['phone'] ?? '',
            'type' => 1,
            'subtotal' => $subTotal,
            'total' => $total,
            'status' => 1
        ]);

       
        TempAdDetail::where('temp_ad_id', '=', $getUsersCart->id)->delete();

        DB::commit();

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'subtotal' => $subTotal,
                    'total' => $subTotal
                ],
                'cart_details' => $getUsersCartDetails,
                'payment_info' => $paymentInfo
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }
}
