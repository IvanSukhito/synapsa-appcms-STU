<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\AccessLogin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
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
            'city_id' => 'required',
            'district_id' => '',
            'sub_district_id' => '',
            'fullname' => 'required',
            'address' => '',
            'address_detail' => '',
            'zip_code' => '',
            'dob' => 'required',
            'gender' => 'required',
            'nik' => 'required',
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
        if ($this->request->get('image')) {
            try {
                $image = base64_to_jpeg($this->request->get('upload_ktp'));
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
            $users = new Users();
            $users->klinik_id = $this->request->get('klinik_id');
            $users->city_id = $this->request->get('city_id');
            $users->district_id = $this->request->get('district_id');
            $users->sub_district_id = $this->request->get('sub_district_id');
            $users->fullname = $this->request->get('fullname');
            $users->address = $this->request->get('address');
            $users->address_detail = $this->request->get('address_detail');
            $users->zip_code = $this->request->get('zip_code');
            $users->dob = $this->request->get('dob');
            $users->gender = $this->request->get('gender');
            $users->nik = $this->request->get('nik');
            $users->phone = $this->request->get('phone');
            $users->email = $this->request->get('email');
            $users->password = bcrypt($this->request->get('password'));
            $users->status = 80;
            $users->patient = $this->request->get('patient');
            $users->upload_ktp = $getUploadKtp;
            $users->image = $getUploadImage;
            $users->save();

            //Insert to table Users_Address
            $addressDetail = [
                'address' => $users->address,
                'address_detail' => $users->address_detail,
                'city_id' => $users->city_id,
                'district_id' => $users->district_id,
                'sub_district_id' => $users->sub_district_id,
                'zip_code' => $users->zip_code,
            ];
            $usersAddress = new UsersAddress();
            $usersAddress->user_id = $users->id;
            $usersAddress->city_id = $users->city_id;
            $usersAddress->district_id = $users->district_id;
            $usersAddress->sub_district_id = $users->sub_district_id;
            $usersAddress->zip_code = $users->zip_code;
            $usersAddress->address_name = $users->address;
            $usersAddress->address = $users->address_detail;
            $usersAddress->address_detail = json_encode($addressDetail);
            $usersAddress->save();

            return response()->json([
                'message' => ['Data Berhasil Dimasukan'],
                'data' => [
                    'klinik_id' => $users->klinik_id,
                    'fullname' => $users->fullname,
                    'address' => $users->address,
                    'address_detail' => $users->address_detail,
                    'zip_code' => $users->zip_code,
                    'gender' => $users->gender,
                    'dob' => $users->dob,
                    'nik' => $users->nik,
                    'phone' => $users->phone,
                    'email' => $users->email,
                    'patient' => $users->patient,
                    'doctor' => $users->doctor,
                    'nurse' => $users->nurse
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

                $getToken = $this->request->get('token');
                if ($getToken && strlen($getToken) > 5) {
                    $getDeviceToken = DeviceToken::firstOrCreate([
                        'token' => $getToken
                    ]);
                    $user->getDeviceToken()->sync([$getDeviceToken->id]);
                }

                $getKlinik = Klinik::where('id', $user->klinik_id)->first();

                $result = [
                    'klinik_id' => $user->klinik_id,
                    'klinik_name' => $getKlinik ? $getKlinik->name : '',
                    'fullname' => $user->fullname,
                    'address' => $user->address,
                    'address_detail' => $user->address_detail,
                    'zip_code' => $user->zip_code,
                    'gender' => intval($user->gender) == 1 ? 1 : 2,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'patient' => $user->patient,
                    'doctor' => $user->doctor,
                    'nurse' => $user->nurse,
                    'status' => $user->status,
                    'status_nice' => $user->status_nice,
                    'gender_nice' => $user->gender_nice
                ];

                if ($user->doctor == 1) {
                    $getDoctor = Doctor::selectRaw('formal_edu, nonformal_edu, doctor_category_id, doctor_category.name AS doctor_category')
                        ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id')
                        ->where('user_id', $user->id)->first();
                    $result['info_doctor'] = $getDoctor;
                }

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

    public function searchCity()
    {
        $this->limit = 500;
        $s = $this->request->get('s');

        $getData = City::query();

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit)
        ]);
    }

    public function searchDistrict()
    {
        $this->limit = 500;
        $s = $this->request->get('s');
        $cityId = intval($this->request->get('city_id'));

        $getData = District::query();
//        if ($cityId > 0) {
//            $getData = $getData->where('city_id', $cityId);
//        }

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit)
        ]);
    }

    public function searchSubdistrict()
    {
        $this->limit = 500;
        $s = $this->request->get('s');
        $districtId = intval($this->request->get('district_id'));

        $getData = SubDistrict::query();
//        if ($districtId > 0) {
//            $getData = $getData->where('district_id', $districtId);
//        }

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit)
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
