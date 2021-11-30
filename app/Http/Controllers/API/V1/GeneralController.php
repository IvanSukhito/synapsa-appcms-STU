<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\AccessLogin;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\ForgetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class GeneralController extends Controller
{
    protected $request;
    protected $accessLogin;
    protected $limit;

    public function __construct(Request $request, AccessLogin $accessLogin)
    {
        $this->request = $request;
        $this->accessLogin = $accessLogin;
        $this->limit = 10;
    }

    public function redirectApps()
    {
        return 'Return to Apps';
    }

    public function signUp()
    {
        $validator = Validator::make($this->request->all(), [
            'klinik_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => '',
            'sub_district_id' => '',
            'fullname' => 'required',
            'address' => '',
            'address_detail' => '',
            'zip_code' => '',
            'dob' => 'required',
            'gender' => 'required',
            'nik' => 'required|unique:users,nik',
            'upload_ktp' => 'required',
            'image' => 'required',
            'phone' => 'required|regex:/^(8\d+)/|numeric|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6'
        ]);
        $validator->setAttributeNames([
            'dob' => 'Anda harus berusia 18 tahun untuk melanjutkan',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $getUploadKtp = '';
        if ($this->request->get('upload_ktp')) {
            try {
                $image = base64_to_jpeg($this->request->get('upload_ktp'));
                $destinationPath = 'synapsaapps/users';
                $set_file_name = md5('image' . strtotime('now') . rand(0, 100)) . '.jpg';
                $getFile = Storage::put($destinationPath . '/' . $set_file_name, $image);
                if ($getFile) {
                    $getImage = $destinationPath . '/' . $set_file_name;
                    $getUploadKtp = $getImage;
                } else {
                    return response()->json([
                        'success' => 0,
                        'token' => $this->request->attributes->get('_refresh_token'),
                        'message' => ['Gagal Mengunggah KTP'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Gagal Mengunggah KTP'],
                    'error' => $e->getMessage()
                ], 422);
            }
        }

        $getUploadImage = '';
        if ($this->request->file('image')) {
            try {
                $image = base64_to_jpeg($this->request->file('image'));
                $destinationPath = 'synapsaapps/users';
                $set_file_name = md5('image' . strtotime('now') . rand(0, 100)) . '.jpg';
                $getFile = Storage::put($destinationPath . '/' . $set_file_name, $image);
                if ($getFile) {
                    $getImage = $destinationPath . '/' . $set_file_name;
                    $getUploadImage = $getImage;
                } else {
                    return response()->json([
                        'success' => 0,
                        'token' => $this->request->attributes->get('_refresh_token'),
                        'message' => ['Gagal Mengunggah Foto'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'token' => $this->request->attributes->get('_refresh_token'),
                    'message' => ['Gagal Mengunggah Foto'],
                ], 422);
            }
        }

        try {

            $userLogic = new UserLogic();
            $getUser = $userLogic->userCreatePatient([
                'klinik_id' => $this->request->get('klinik_id'),
                'province_id' => $this->request->get('province_id'),
                'city_id' => $this->request->get('city_id'),
                'district_id' => $this->request->get('district_id'),
                'sub_district_id' => $this->request->get('sub_district_id'),
                'fullname' => $this->request->get('fullname'),
                'address' => $this->request->get('address'),
                'address_detail' => $this->request->get('address_detail'),
                'zip_code' => $this->request->get('zip_code'),
                'dob' => $this->request->get('dob'),
                'gender' => $this->request->get('gender'),
                'nik' => $this->request->get('nik'),
                'phone' => $this->request->get('phone'),
                'email' => $this->request->get('email'),
                'password' => $this->request->get('password'),
                'upload_ktp' => $getUploadKtp,
                'image' => $getUploadImage,
                'status' => 80
            ]);

            return response()->json([
                'message' => ['Data Berhasil Dimasukan'],
                'data' => [
                    'user_id' => $getUser->id,
                    'klinik_id' => $getUser->klinik_id,
                    'fullname' => $getUser->fullname,
                    'address' => $getUser->address,
                    'address_detail' => $getUser->address_detail,
                    'zip_code' => $getUser->zip_code,
                    'gender' => $getUser->gender,
                    'dob' => $getUser->dob,
                    'nik' => $getUser->nik,
                    'phone' => $getUser->phone,
                    'email' => $getUser->email,
                    'patient' => $getUser->patient,
                    'doctor' => $getUser->doctor,
                    'nurse' => $getUser->nurse
                ]
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => ['Memasukan Gagal'],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $user = Users::where('email', $this->request->get('email'))->first();
        if ($user) {
            if (!app('hash')->check($this->request->get('password'), $user->password)) {
                $user = false;
            }
        }

        if ($user) {

            try {
                $token = JWTAuth::fromUser($user);

                $userLogic = new UserLogic();

                $getToken = $this->request->get('fcm_token');
                if ($getToken && strlen($getToken) > 5) {
                    $userLogic->updateToken($user->id, $getToken);
                }

                $result = $userLogic->userInfo($user->id, $user);

                return response()->json([
                    'success' => 1,
                    'message' => ['Sukses Login'],
                    'data' => $result,
                    'token' => (string)$token
                ]);
            } catch (JWTException $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Token Gagal dibuat'],
                ], 422);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Token Gagal dibuat'],
                ], 422);
            }
        } else {
            return response()->json([
                'success' => 0,
                'message' => [__('Gagal Login')],
            ], 422);
        }
    }

    public function forgotPassword()
    {
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }
        $getEmail = $this->request->get('email');
        $getUser = Users::where('email', $getEmail)->first();
        if (!$getUser) {
            return response()->json([
                'success' => 0,
                'message' => [__('Alamat Surel Tidak Ditemukan')]
            ], 422);
        }

        if ($getUser->status != 80) {
            return response()->json([
                'success' => 0,
                'message' => [__('Akun tidak Aktif')]
            ], 422);
        }

        $getForgetPassword = ForgetPassword::where('users_id', $getUser->id)->where('status', 1)->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-1 hour")))->first();
        if ($getForgetPassword) {
            return response()->json([
                'success' => 0,
                'message' => [__('Akun Sudah Meminta Lupa Sandi, Tunggu 1 Jam untuk meminta ulang lagi.')]
            ], 422);
        }

        $newCode = generateNewCode(6);

        ForgetPassword::create([
            'users_id' => $getUser->id,
            'code' => $newCode,
            'email' => $getEmail,
            'attempt' => 0,
            'status' => 1
        ]);

        $subject = 'Lupa Password';

        Mail::send('mail.forgot', [
            'user' => $getUser,
            'code' => $newCode
        ], function ($m) use ($getUser, $subject) {
            $m->to($getUser->email, $getUser->name)->subject($subject);
        });

        return response()->json([
            'success' => 1,
            'message' => ['Berhasil mengirimkan Kata Sandi baru ke email anda'],
        ]);

    }

    public function searchKlinik()
    {
        $s = $this->request->get('s');

        $getData = Klinik::query();

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit)
        ]);

    }

    public function searchProvince(){

        $s = $this->request->get('s');

        $getData = Province::query();

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->get()
        ]);
    }

    public function searchCity()
    {
        $s = $this->request->get('s');
        $provinceId = intval($this->request->get('province_id'));

        $getData = City::query();

        if ($provinceId) {
            $getData = $getData->Where('province_id', 'LIKE', strip_tags($provinceId))->orWhere('name', 'LIKE', strip_tags($s));
        }
        else{
            if ($s) {
                $getData = $getData->where('name', 'LIKE', strip_tags($s));
            }
        }


        return response()->json([
            'success' => 1,
            'data' => $getData->get()
        ]);
    }

    public function searchDistrict()
    {

        $s = $this->request->get('s');
        $cityId = intval($this->request->get('city_id'));

        $getData = District::query();

        if ($cityId) {
            $getData = $getData->Where('city_id', 'LIKE', strip_tags($cityId))->orWhere('name', 'LIKE', strip_tags($s));
        }
        else{
            if ($s) {
                $getData = $getData->where('name', 'LIKE', strip_tags($s));
            }
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->get()
        ]);
    }

    public function searchSubdistrict()
    {

        $s = $this->request->get('s');
        $districtId = intval($this->request->get('district_id'));

        $getData = SubDistrict::query();

        if ($districtId) {
            $getData = $getData->Where('district_id', 'LIKE', strip_tags($districtId))->orWhere('name', 'LIKE', strip_tags($s));
        }
        else{
            if ($s) {
                $getData = $getData->where('name', 'LIKE', strip_tags($s));
            }
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->get()
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            JWTAuth::invalidate($token);
        } catch (JWTException $e) {

        } catch (\Exception $e) {

        }

        return response()->json([
            'success' => 1
        ]);
    }

    public function version()
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        return response()->json([
            'success' => 1,
            'ios' => [
                'version' => isset($setting['ios-version']) ? $setting['ios-version'] : '',
                'url' => isset($setting['ios-url']) ? $setting['ios-url'] : ''
            ],
            'android' => [
                'version' => isset($setting['android-version']) ? $setting['android-version'] : '',
                'url' => isset($setting['android-url']) ? $setting['android-url'] : ''
            ]
        ]);

    }

    public function compareVersion()
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $type = $this->request->get('type');
        $version = $this->request->get('version');
        $valid = 0;
        $urlPath = '';
        if ($type == 'ios') {
            $getVersion = isset($setting['ios-version']) ? $setting['ios-version'] : '';
            if (version_compare($version, $getVersion) >= 0) {
                $valid = 1;
            } else {
                $urlPath = isset($setting['ios-url']) ? $setting['ios-url'] : '';
            }
        } else {
            $getVersion = isset($setting['android-version']) ? $setting['android-version'] : '';
            if (version_compare($version, $getVersion) >= 0) {
                $valid = 1;
            } else {
                $urlPath = isset($setting['android-url']) ? $setting['android-url'] : '';
            }
        }

        return response()->json([
            'success' => $valid,
            'valid_version' => $getVersion,
            'your_version' => $version,
            'url' => $urlPath
        ]);

    }

}
