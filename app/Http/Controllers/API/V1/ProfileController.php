<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Notifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class ProfileController extends Controller
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

    public function profile()
    {
        $user = $this->request->attributes->get('_user');

        $getUser = $user;
        $getUser->image_full = strlen($user->image) > 0 ? asset('uploads/users/'.$user->image) : null;
        $getUser->join = date('d F Y', strtotime($user->created_at));


        return response()->json([
            'success' => 1,
            'data' => $getUser,
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

    }

    public function updateProfile()
    {
        $user = $this->request->attributes->get('_user');

        $user = Users::where('id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'fullname' => 'required',
            'dob' => 'required|date|before:'.date('Y-m-d', strtotime("-18 years")),
            'gender' => 'required',
            'nik' => 'required',
            'upload_ktp' => 'required',
            'phone' => 'required|regex:/^(08\d+)/|numeric|unique:users,phone',
            'email' => 'required|email|unique:users,email',
        ], [
            'before' => ':attribute'
        ]);
        $validator->setAttributeNames([
            'dob' => 'Anda harus berusia 18 tahun untuk melanjutkan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token')
            ]);
        }

        $getUploadKtp = '';
        if ($this->request->get('upload_ktp')) {
            try {
                $image = base64_to_jpeg($this->request->get('upload_ktp'));
                $destinationPath = 'uploads/users';
                $set_file_name = md5('upload_ktp'.strtotime('now').rand(0, 100)).'.jpg';
                file_put_contents($destinationPath.'/'.$set_file_name, $image);

                $getUploadKtp = $set_file_name;

                $img = Image::make('./'.$destinationPath.'/'.$set_file_name);
                $img->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->rotate(-90);
                $img->save();
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Failed upload KTP Image'],
                ]);
            }
        }



        $user->fullname = $this->request->get('fullname');
        $user->dob = $this->request->get('dob');
        $user->gender = $this->request->get('gender');
        $user->nik = $this->request->get('nik');
        $user->upload_ktp = $getUploadKtp;
        $user->email = $this->request->get('email');
        $user->phone = $this->request->get('phone');
        $user->save();

        return response()->json([
            'success' => 1,
            'data' => [
                'klinik_id' => $user->klinik_id,
                'fullname' => $user->fullname,
                'address' => $user->address,
                'address_detail' => $user->address_detail,
                'zip_code' => $user->zip_code,
                'gender' => $user->gender,
                'dob' => $user->dob,
                'nik' => $user->nik,
                'phone' => $user->phone,
                'email' => $user->email,
                'patient' => $user->patient,
                'doctor' => $user->doctor,
                'nurse' => $user->nurse
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Success Update Profile'],
        ]);

    }

    public function updatePhoto()
    {
        $user = $this->request->attributes->get('_user');

        $user = Users::where('id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'image' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        try {
            $image = base64_to_jpeg($this->request->get('image'));
            $destinationPath = 'uploads/users';
            $set_file_name = md5('image'.strtotime('now').rand(0, 100)).'.jpg';
            file_put_contents($destinationPath.'/'.$set_file_name, $image);
            $getImage = $set_file_name;

            $img = Image::make('./'.$destinationPath.'/'.$set_file_name);
            $img->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->rotate();
            $img->save();

        }
        catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Failed upload Image']
            ]);
        }

        if ($getImage) {
            $user->image = $getImage;
            $user->save();

            $getUser = [
                'image' => asset($destinationPath.'/'.$set_file_name)
            ];

            return response()->json([
                'success' => 1,
                'data' => $getUser,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Success Update Photo Profile'],
            ]);
        }
        else {

            return response()->json([
                'success' => 0,
                'message' => ['failed to upload'],
                'token' => $this->request->attributes->get('_refresh_token')
            ]);
        }
    }

    public function getAddress(){
        $user = $this->request->attributes->get('_user');

        $getUser = $user;
        $getUser->join = date('d F Y', strtotime($user->created_at));

        $getUser = [

            'address' => $user->address,
            'address_detail' => $user->address_detail,
            'city' => $user->city_id,
            'district' => $user->district_id,
            'sub_district' => $user->sub_district_id,
            'zip_code' => $user->zip_code
        ];


        return response()->json([
            'success' => 1,
            'data' => $getUser,
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

    }

    public function updateAddress(){

        $user = $this->request->attributes->get('_user');

        $user = Users::where('id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'address' => 'required',
            'address_detail' => 'required',
            'zip_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }


            $user->address = $this->request->get('address');
            $user->address_detail = $this->request->get('address_detail');
            $user->city_id = $this->request->get('city_id');
            $user->district_id = $this->request->get('district_id');
            $user->sub_district_id = $this->request->get('sub_district_id');
            $user->zip_code = $this->request->get('zip_code');
            $user->save();


            $getUser = [

                'address' => $user->address,
                'address_detail' => $user->address_detail,
                'city' => $user->city_id,
                'district' => $user->district_id,
                'sub_district' => $user->sub_district_id,
                'zip_code' => $user->zip_code
            ];

            return response()->json([
                'success' => 1,
                'data' => $getUser,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Success Update Address Profile'],
            ]);


    }


    public function updatePassword()
    {
        $user = $this->request->attributes->get('_user');
        $user = Users::where('id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'old_password' => 'required|min:6',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token')
            ]);
        }

        if(!app('hash')->check($this->request->get('old_password'), $user->password)) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Old Password not match'],
            ]);
        }

        $user->password = bcrypt($this->request->get('password'));
        $user->save();


        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Success Update Password'],
        ]);
    }

    public function verifEmail(){

        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ]);
        }

        $user = Users::where('id', $user->id)->first();
        $getEmail = $this->request->get('email');

        if($getEmail == $user->email){

            return response()->json([
                'success' => 1,
                'message' => ['Success send link verification to your email'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else{
            return response()->json([
                'success' => 0,
                'message' => ['Email not match'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function verifPhone(){

        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|regex:/^(08\d+)/|numeric'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ]);
        }

        $user = Users::where('id', $user->id)->first();
        $getPhone = $this->request->get('phone');


        if($getPhone == $user->phone){

            return response()->json([
                'success' => 1,
                'message' => ['Success send link verification to your phone'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else{
            return response()->json([
                'success' => 0,
                'message' => ['Phone not match'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function notifications(){
        $user = $this->request->attributes->get('_user');

        $data = [];

        $limit = 4;

        $totalNotif = Notifications::where('user_id',$user->id)->where('is_read',1)->count();
        $notif = Notifications::where('user_id',$user->id)->where('is_read',1)->paginate($limit);

        return response()->json([
            'success' => 1,
            'data' => [

                'totalNotif' => $totalNotif,
                'Notifications' => $notif,

            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
