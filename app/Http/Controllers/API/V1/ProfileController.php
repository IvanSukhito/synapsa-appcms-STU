<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Notifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ProfileController extends Controller
{
    protected $request;
    protected $setting;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function profile()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        $result = $userLogic->userInfo($user->id);

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

    }

    public function updateProfile()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'fullname' => 'required',
            'dob' => 'required|date|before:'.strtotime(date('Y-m-d', strtotime("-18 years"))),
            'gender' => 'required',
            'nik' => 'required',
            'upload_ktp' => '',
            'phone' => 'required|regex:/^(8\d+)/|numeric|unique:users,phone,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);
        $validator->setAttributeNames([
            'dob' => 'Anda harus berusia 18 tahun untuk melanjutkan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token')
            ], 422);
        }

        $getUploadKtp = '';
        if ($this->request->file('upload_ktp')) {
             try {
                 $image = base64_to_jpeg($this->request->file('upload_ktp'));
                 $destinationPath = 'synapsaapps/users';
                 $set_file_name = md5('image'.strtotime('now').rand(0, 100)).'.jpg';
                 $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
                 if ($getFile) {
                     $getImage = $destinationPath.'/'.$set_file_name;
                     $getUploadKtp = $getImage;
                 }
                 else {
                     return response()->json([
                         'success' => 0,
                         'token' => $this->request->attributes->get('_refresh_token'),
                         'message' => ['Gagal Mengunggah Foto'],
                     ], 422);
                 }
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Gagal Mengunggah Foto KTP'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }
        }

        $saveData = [
            'fullname' => $this->request->get('fullname'),
            'dob' => $this->request->get('dob'),
            'gender' => $this->request->get('gender'),
            'nik' => $this->request->get('nik'),
            'email' => $this->request->get('email'),
            'phone' => $this->request->get('phone'),
            'upload_ktp' => $getUploadKtp
        ];
        if (strlen($getUploadKtp) > 0) {
            $saveData['upload_ktp'] = $getUploadKtp;
        }

        $userLogic = new UserLogic();
        $result = $userLogic->userUpdatePatient($user->id, $saveData);

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil Memperbarui profil'],
        ]);

    }

    public function updatePhoto()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'image' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        try {
            $image = base64_to_jpeg($this->request->file('image'));
            $destinationPath = 'synapsaapps/users';
            $set_file_name = md5('image'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = $destinationPath.'/'.$set_file_name;

                $userLogic = new UserLogic();
                $result = $userLogic->userUpdatePatient($user->id, ['image' => $getImage]);

                return response()->json([
                    'success' => 1,
                    'data' => $result,
                    'token' => $this->request->attributes->get('_refresh_token'),
                    'message' => ['Berhasil Memperbarui Foto Profile'],
                ]);

            }
            else {
                return response()->json([
                    'success' => 0,
                    'token' => $this->request->attributes->get('_refresh_token'),
                    'message' => ['Gagal Mengunggah Foto'],
                ], 422);
            }
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Gagal Mengunggah Foto'],
                'error' => $e->getMessage(),
            ], 422);
        }

    }

    public function getAddress(){
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id, $user->phone),
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

    }

    public function updateAddress(){

        $user = $this->request->attributes->get('_user');

        $user = Users::where('id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'address' => 'required',
            'address_detail' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'sub_district_id' => 'required',
            'zip_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $userLogic = new UserLogic();

        $result = $userLogic->userUpdateAddressPatient($user->id, [
            'address' => strip_tags($this->request->get('address')),
            'address_detail' => strip_tags($this->request->get('address_detail')),
            'province_id' => intval($this->request->get('province_id')),
            'city_id' => intval($this->request->get('city_id')),
            'district_id' => intval($this->request->get('district_id')),
            'sub_district_id' => intval($this->request->get('sub_district_id')),
            'zip_code' => strip_tags($this->request->get('zip_code'))
        ]);

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Sukses Memperbarui Alamat Profil'],
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
            ], 422);
        }

        if(!app('hash')->check($this->request->get('old_password'), $user->password)) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Kata Sandi Lama Tidak Cocok'],
            ],422);
        }

        $userLogic = new UserLogic();
        $userLogic->userUpdatePatient($user->id, ['password' => $this->request->get('password')], $user);

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil Memperbarui Kata Sandi'],
        ]);
    }

    public function verifEmail(){

        $user = $this->request->attributes->get('_user');

        $verify = $this->request->get('verify');

        $getUser = Users::where('email', $user->email)->first();

        if (!$getUser) {
            return response()->json([
                'success' => 0,
                'message' => [__('Email Tidak Ditemukan')]
            ], 422);
        }elseif ($getUser->verification_email == 1){
            return response()->json([
                'success' => 0,
                'message' => [__('Email Sudah Diverifikasi')]
            ], 422);
        }


        if($verify == 1){

            $subject = 'Verification Email';

            Mail::send('mail.verify', [
                'user' => $getUser,
            ], function ($m) use ($getUser, $subject) {
                $m->to($getUser->email, $getUser->name)->subject($subject);
            });

            return response()->json([
                'success' => 1,
                'message' => ['Berhasil Mengirim Link Verifikasi Kepada Email Anda'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else{
            return response()->json([
                'success' => 0,
                'message' => ['Email Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

    }
    public function updateVerifEmail($id){

        $getUser = Users::where('id', $id)->first();

        if (!$getUser) {
            return response()->json([
                'success' => 0,
                'message' => [__('Email Tidak Ditemukan')]
            ], 422);
        }

       $getUser->verification_email = 1;
       $getUser->save();

       return view('success_verif');

    }

    public function verifPhone(){

        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|regex:/^(8\d+)/|numeric'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $user = Users::where('id', $user->id)->first();
        $getPhone = $this->request->get('phone');


        if($getPhone == $user->phone){
            return response()->json([
                'success' => 1,
                'message' => ['Berhasil Mengirim Link Verifikasi Kepada Nomor Handphone Anda'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else{
            return response()->json([
                'success' => 0,
                'message' => ['Nomor Handphone Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

    }

    public function notifications(){
        $user = $this->request->attributes->get('_user');

        $limit = intval($this->request->get('limit'));
        if ($limit <= 0) {
            $limit = 10;
        }

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

    public function uploadImage()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'image' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        try {
            $image = base64_to_jpeg($this->request->get('image'));
            $destinationPath = 'synapsaapps/chat/user_'.$user->id;
            $set_file_name = md5('image'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = $destinationPath.'/'.$set_file_name;

                return response()->json([
                    'success' => 1,
                    'data' => env('OSS_URL').'/'.$getImage,
                    'file' => $getImage,
                    'token' => $this->request->attributes->get('_refresh_token'),
                    'message' => ['Berhasil Memasukan Gambar'],
                ]);

            }
            else {
                return response()->json([
                    'success' => 0,
                    'token' => $this->request->attributes->get('_refresh_token'),
                    'message' => ['Gagal Mengunggah Gambar'],
                ], 422);
            }
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Gagal Mengunggah Gambar'],
                'error' => $e->getMessage(),
            ], 422);
        }
    }

}
