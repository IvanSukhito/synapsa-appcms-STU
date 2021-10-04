<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Notifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


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

        $getUser = $user;
        $getUser->image_full = strlen($user->image) > 0 ? env('OSS_URL').'/'.$user->image : asset('assets/cms/images/no-img.png');
        $getUser->upload_ktp_full = strlen($user->upload_ktp) > 0 ? env('OSS_URL').'/'.$user->upload_ktp : asset('assets/cms/images/no-img.png');
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
        if ($this->request->get('upload_ktp')) {
             try {
                 $image = base64_to_jpeg($this->request->get('upload_ktp'));
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

        $user->fullname = $this->request->get('fullname');
        $user->dob = $this->request->get('dob');
        $user->gender = $this->request->get('gender');
        $user->nik = $this->request->get('nik');
        if (strlen($getUploadKtp) > 0) {
            $user->upload_ktp = $getUploadKtp;
        }
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
                'nurse' => $user->nurse,
                'image' => $user->upload_ktp_full
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil Memperbarui profil'],
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
            ], 422);
        }

        try {
            $image = base64_to_jpeg($this->request->get('image'));
            $destinationPath = 'synapsaapps/users';
            $set_file_name = md5('image'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = $destinationPath.'/'.$set_file_name;
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

        if ($getImage) {
            $user->image = $getImage;
            $user->save();

            $getUser = [
                'image' => env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name
            ];

            return response()->json([
                'success' => 1,
                'data' => $getUser,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil Memperbarui Foto Profile'],
            ]);
        }
        else {

            return response()->json([
                'success' => 0,
                'message' => ['Gagal Untuk Mengunggah'],
                'token' => $this->request->attributes->get('_refresh_token')
            ], 422);
        }
    }

    public function getAddress(){
        $user = $this->request->attributes->get('_user');

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
            ], 422);
        }

        $user->address = strip_tags($this->request->get('address'));
        $user->address_detail = strip_tags($this->request->get('address_detail'));
        $user->city_id = intval($this->request->get('city_id'));
        $user->district_id = intval($this->request->get('district_id'));
        $user->sub_district_id = intval($this->request->get('sub_district_id'));
        $user->zip_code = strip_tags($this->request->get('zip_code'));
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

        $user->password = bcrypt($this->request->get('password'));
        $user->save();

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil Memperbarui Kata Sandi'],
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
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $user = Users::where('id', $user->id)->first();
        $getEmail = $this->request->get('email');

        if($getEmail == $user->email){

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

}
