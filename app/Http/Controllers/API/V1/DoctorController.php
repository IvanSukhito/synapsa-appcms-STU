<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\JanjiDoktor;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\UsersCartDetail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    protected $request;
    protected $setting;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getDoctor(){

        $user = $this->request->attributes->get('_user');

        $limit = 4;
       
      
        $data = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, users.image as image,doctor_category.name as doctor_category, doctor.price as doctor_price')
        ->join('doctor_category','doctor_category.id', '=', 'doctor.doctor_category_id')
        ->join('users','users.id','=','doctor.user_id')->where('users.doctor', '=', 1); 
     
        $getData = $data->limit($limit)->get();

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getDoctorDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Doctor::where('id', $id)->first();
        $dataUser = Users::where('id', $data->user_id)->first();
        $dataCategory = DoctorCategory::where('id', $data->doctor_category_id)->first();

        $getData = [    
            'doctor_name' => $dataUser->fullname,
            'doctor_category' => $dataCategory->name,
            'doctor_price' => $data->price,
            'name' => $dataUser->fullname,
            'dob' => $dataUser->dob,
            'gender' => $dataUser->gender,
            'address' => $dataUser->address,
            'phone' => $dataUser->phone,
            'formal_edu' => $data->formal_edu,
            'nonformal_edu' => $data->nonformal_edu
        ];
     
        if (!$data) {
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => 'Doctor Not Found',
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $getData,
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
            //$getUsersCart = UsersCart::where('users_id', $user->id)->first();

            //if(!$getUsersCart){

            //    $UsersCart = UsersCart::FirstOrCreate([
            //        'users_id' => $user->id,
            //    ]);
//
            //    $cart = new UsersCartDetail();
            //    $cart->janji_temu_doctor_id = $janjiTemu->id;
            //    $cart->users_cart_id = $UsersCart->id;
            //    $cart->qty = $this->request->get('qty');
            //    $cart->save();
            //}
            //else
            //{
            //    $cart = new UsersCartDetail();
            //    $cart->janji_temu_doctor_id = $this->request->get('product_id');
            //    $cart->users_cart_id = $getUsersCart->id;
            //    $cart->qty = $this->request->get('qty');
            //    $cart->save();
            //}
            $janjiTemu = new JanjiDoktor();
            $janjiTemu->doctor_id = $id;
            $janjiTemu->book_day = $this->request->get('book_day');
            $janjiTemu->book_at = $this->request->get('book_at');
            $janjiTemu->save();

            if($janjiTemu){
            $getUsersCart = UsersCart::where('users_id', $user->id)->first();

            if(!$getUsersCart){
                $UsersCart = UsersCart::FirstOrCreate([
                    'users_id' => $user->id,
                ]);
//
                $cart = new UsersCartDetail();
                $cart->janji_temu_doctor_id = $janjiTemu->id;
                $cart->product_id = 0;
                $cart->users_cart_id = $UsersCart->id;
                $cart->qty = 1;
                $cart->save();
            }
            else
            {
                $cart = new UsersCartDetail();
                $cart->janji_temu_doctor_id = $janjiTemu->id;
                $cart->product_id = 0;
                $cart->users_cart_id = $getUsersCart->id;
                $cart->qty = 1;
                $cart->save();
            }
            }

            return response()->json([
                'success' => 1,
                'data' => $janjiTemu,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
     

    }
}
