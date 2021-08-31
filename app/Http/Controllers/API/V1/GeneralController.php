<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\AccessLogin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\ForgetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class GeneralController extends Controller
{
    protected $request;
    protected $accessLogin;

    public function __construct(Request $request, AccessLogin $accessLogin)
    {
        $this->request = $request;
        $this->accessLogin = $accessLogin;
    }

    public function signUp()
    {
        $validator = Validator::make($this->request->all(), [
            'klinik_id' => 'required',
            'city_id' => '',
            'district_id' => '',
            'sub_district_id' => '',
            'fullname' => 'required',
            'address' => 'required',
            'address_detail' => 'required',
            'zip_code' => 'required',
            'dob' => 'required',
            'gender' => 'required',
            'nik' => 'required',
            'upload_ktp_full' => 'required',
            'phone' => 'required',
            //'email' => 'required',
            //'password' => 'required',

            'email' => 'required|email',
            'password' => 'required|',
        ]);

        $getKlinik = $this->request->get('klinik_id');
        $getCity = $this->request->get('city_id');
        $getDistrict = $this->request->get('district_id');
        $getSubDistrict = $this->request->get('sub_district_id');
        $getFullName = $this->request->get('fullname');
        $getAddress = $this->request->get('address');
        $getAddressDetail = $this->request->get('address_detail');
        $getZipCode = $this->request->get('zip_code');   
        $getDob = $this->request->get('dob');
        $getGender = $this->request->get('gender');
        $getNik = $this->request->get('nik');
        $getUploadKtp = $this->request->get('upload_ktp_full');
        $getPhone = $this->request->get('phone');
        $getEmail = $this->request->get('email');
        $getPass = $this->request->get('password');
        $getPassConfirm = $this->request->get('password_confirmation');
        
        //$dokument = $this->request->file('upload_ktp');
        //$userFolder = 'user_' . preg_replace("/[^A-Za-z0-9?!]/", '', $getNik);
        //$todayDate = date('Y-m-d');
        //$folderName = $userFolder . '/kegiatan/' . $todayDate . '/';
        // $Dokument = [];        
        // foreach ($dokument as $listDoc) {
        //     if ($listDoc->getError() == 0) {
        //         $getFileName = $listDoc->getClientOriginalName();
        //         $ext = explode('.', $getFileName);
        //         $fileName = reset($ext);
        //         $ext = end($ext);
        //         $setFileName = preg_replace("/[^A-Za-z0-9?!]/", '_', $fileName) . '_' . date('His') . rand(0,100) . '.' . $ext;
        //         $destinationPath = './uploads/' . $folderName  . '/';
        //         $destinationLink = 'uploads/' . $folderName . '/' . $setFileName;
        //         $listDoc->move($destinationPath, $setFileName);
                //
        //         $Dokument[] = [
        //             'name' => $setFileName,
        //             'path' => $destinationLink
        //         ];
        //     }
        // }
        if($getPass == $getPassConfirm){

            try{
                $users = new Users();
                $users->klinik_id = $getKlinik;
                $users->city_id = $getCity;
                $users->district_id = $getDistrict;
                $users->sub_district_id = $getSubDistrict;
                $users->fullname = $getFullName;
                $users->address = $getAddress;
                $users->address_detail = $getAddressDetail;
                $users->zip_code = $getZipCode;
                $users->dob = $getDob;
                $users->gender = $getGender;
                $users->nik = $getNik;
                $users->upload_ktp = $getUploadKtp; // json_encode($Dokument);
                $users->phone = $getPhone;
                $users->email = $getEmail;
                $users->password = Hash::make($getPass);
                $users->save();
                
                return response()->json([
                    'message' => 'Data Has Been Inserted',
                    'data' => $users
                ]);

            }

        
        catch (QueryException $e){
            return response()->json([
                'message' => 'Insert Failed'
             
            ]);
        }
    }   

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
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

                return response()->json([
                    'success' => 1,
                    'data' => [
                        'klinik_id' => $user->klinik_id,
                        'fullname' => $user->fullname,
                        'address' => $user->address,
                        'address_detail' => $user->address_detail,
                        'zip_code' => $user->zip_code,
                        'gender' => $user->gender,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'patient' => $user->patient,
                        'doctor' => $user->doctor,
                        'nurse' => $user->nurse
                    ],
                    'token' => (string)$token
                ]);
            }
            catch (JWTException $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Token Gagal dibuat'],
                ], 422);
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Token Gagal dibuat'],
                ], 422);
            }
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => [__('Gagal Login')],
            ], 422);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            JWTAuth::invalidate($token);
        }
        catch (JWTException $e) {

        }
        catch (\Exception $e) {

        }

        return response()->json([
            'success' => 1
        ]);
    }

    public function version()
    {
        $setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
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
        $setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
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
            }
            else {
                $urlPath = isset($setting['ios-url']) ? $setting['ios-url'] : '';
            }
        }
        else {
            $getVersion = isset($setting['android-version']) ? $setting['android-version'] : '';
            if (version_compare($version, $getVersion) >= 0) {
                $valid = 1;
            }
            else {
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
    public function forgotPassword(){
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email'
         
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ]);
        }
        $getEmail = $this->request->get('email');
        $getUser = Users::where('email', $getEmail)->where('status', 2)->first();
        if (!$getUser) {
            return response()->json([
                'success' => 0,
                'message' => [__('Email not found')]
            ]);
        }

        if ($getUser->status != 2) {
            return response()->json([
                'success' => 0,
                'message' => [__('Account not active')]
            ]);
        }

        //$getMaxSend = isset($this->setting['max_send_otp_email']) ? $this->setting['max_send_otp_email'] : 0;
        //$getLimitSend = isset($this->setting['time_next_send_otp_email']) ? $this->setting['time_next_send_otp_email'] : 0;
//
        //$dateStart = date('Y-m-d 00:00:00');
        //$dateEnd = date('Y-m-d 23:59:59');
        //$total = ForgetPassword::where('email', $getEmail)->where('attempt', 2)->where('status', 80)->whereBetween('created_at', [$dateStart, $dateEnd])->count();
        //if ($total >= $getMaxSend) {
        //    return response()->json([
        //        'success' => 0,
        //        'message' => ['you reach maximum forget password per day'],
        //    ]);
        //}

        //$getLast = ForgetPassword::where('email', $getEmail)->where('attempt', 2)->where('status', 80)->orderBy('created_at', 'DESC')->first();
        //$dateNow = strtotime('-'.$getLimitSend.' minutes');
        //$remaining = $getMaxSend - $total;
        //if ($getLast) {
        //    if (strtotime($getLast->created_at) >= $dateNow) {
        //        $time = (strtotime($getLast->created_at) - $dateNow);
        //        $getMinutes = ceil($time / 60);
        //        return response()->json([
        //            'success' => 0,
        //            'data' => [
        //                'time' => $time,
        //                'minutes' => $getMinutes
        //            ],
        //            'message' => ['You must waiting to '.$getMinutes.'(minutes) before receive another OTP / '.$remaining.' remaining'],
        //        ]);
        //    }
        //}

        ForgetPassword::create([
          
            'user_id' => $getUser->id,
            'code' => '0000',
            'email' => $getEmail,
            'attempt' => 2,
            'status' => 80
        ]); 

        if (isset($this->setting['using_sms']) && $this->setting['using_sms'] == 1) {
            $newPassword = generateNewCode(6);

            $emailContent = view('mail.forgot', [
                'user' => $getUser,
                'newPassword' => $newPassword
            ])->render();

            $from = 'no-reply@wecanshop.id';
            $to = $getUser->email;
            $subject = 'Send New Password';
            api_send_email($getUser->id, $getUser->name, $from, $to, $subject, $emailContent);
        }
        else {
            $newPassword = 111111;
        }

        $getUser->password = bcrypt($newPassword);
        $getUser->save();

        return response()->json([
            'success' => 1,
            'message' => ['Berhasil mengirimkan Kata Sandi baru ke email anda'],
        ]);




    }

}
