<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\generateLogic;
use App\Codes\Logic\LabLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\UserAppointmentLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentNurse;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersCart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
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

        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $appointmentLogic = new UserAppointmentLogic();

        return response()->json([
            'success' => 1,
            'data' => $appointmentLogic->appointmentList($user->id, $time, $getLimit),
            'default_image' => asset('assets/cms/images/no-img.png'),
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $type = intval($this->request->get('type'));

        $appointmentLogic = new UserAppointmentLogic();
        $getResult = $appointmentLogic->appointmentInfo($user->id, $user->phone, $id, $type);
        if ($getResult['success'] == 0) {
            switch ($type) {
                case 2 : $message = 'Janji Temu Lab Tidak Ditemukan';
                    break;
                case 3 : $message = 'Janji Temu Nurse Tidak Ditemukan';
                    break;
                default: $message = 'Janji Temu Dokter Tidak Ditemukan';
                    break;
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getResult['data'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function reSchedule($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));

        $getDate = strtotime($this->request->get('date')) > 0 ?
            date('Y-m-d', strtotime($this->request->get('date'))) :
            date('Y-m-d', strtotime("+1 day"));

        if ($getDate < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Waktu sudah lewat'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $appointmentLogic = new UserAppointmentLogic();
        $getResult = $appointmentLogic->appointmentInfo($user->id, $user->phone, $id, $type, [2]);
        if ($getResult['success'] == 0) {
            switch ($type) {
                case 2 : $message = 'Janji Temu Lab Tidak Ditemukan';
                    break;
                case 3 : $message = 'Janji Temu Nurse Tidak Ditemukan';
                    break;
                default: $message = 'Janji Temu Dokter Tidak Ditemukan';
                    break;
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getAppointment = $getResult['data']['data'];

        if ($type == 1) {
            $doctorLogic = new DoctorLogic();
            $data = $doctorLogic->doctorInfo($getAppointment->doctor_id, $getAppointment->service_id);
            if (!$data) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Doktor Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            $getSchedule = $doctorLogic->scheduleDoctorList($getAppointment->doctor_id, $getAppointment->service_id, $getDate);
            $getList = get_list_type_service();
            $getService = Service::where('id', '=', $getAppointment->service_id)->where('status', '=', 80)->first();
            if (!$getService) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Service Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("now")),
                    'schedule_end' => date('Y-m-d', strtotime("+1 year")),
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'date' => $getDate,
                    'schedule' => $getSchedule,
                    'doctor' => $data
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
        else if ($type == 2) {

            $labLogic = new LabLogic();

            $getLabSchedule = $labLogic->scheduleLabList($user->klinik_id, $getAppointment->service_id, $getDate);
            $getList = get_list_type_service();
            $getService = Service::where('id', '=', $getAppointment->service_id)->where('status', '=', 80)->first();
            if (!$getService) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Service Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("now")),
                    'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'date' => $getDate,
                    'schedule' => $getLabSchedule
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }

        return response()->json([
            'success' => 0,
            'message' => ['Janji Temu Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

    }

    public function updateReSchedule($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'schedule_id' => 'numeric|required',
            'type' => 'numeric|required',
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $type = intval($this->request->get('type'));
        $scheduleId = intval($this->request->get('schedule_id'));
        $date = date('Y-m-d', strtotime($this->request->get('date')));

        $appointmentLogic = new UserAppointmentLogic();
        $getResult = $appointmentLogic->reScheduleAppointment($id, $user->id, $type, $scheduleId, $date);
        if ($getResult != 80) {
            if ($getResult == 91) {
                $message = 'Schedule tidak ditemukan';
            }
            else if ($getResult == 92) {
                $message = 'Hari dipilih tidak sama';
            }
            else {
                $message = 'Janji Temu tidak ditemukan';
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil menganti schedule'],
        ]);

    }

    public function fillForm($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $validator = Validator::make($this->request->all(), [
            'body_height' => 'numeric',
            'body_weight' => 'numeric',
            'blood_pressure' => '',
            'body_temperature' => 'numeric',
            'symptoms' => 'required',
            'medical_checkup' => ''
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getMedicalCheckup = $this->request->get('medical_checkup');
        $listMedicalCheckup = [];
        foreach ($getMedicalCheckup as $listImage) {
            $image = base64_to_jpeg($listImage);
            $destinationPath = 'synapsaapps/users/'.$user->id.'/forms';
            $set_file_name = date('Ymd').'_'.md5('medical_checkup'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name;
                $listMedicalCheckup[] = $getImage;
            }
        }

        $saveFormPatient = [
            'body_height' => strip_tags($this->request->get('body_height')),
            'body_weight' => strip_tags($this->request->get('body_weight')),
            'blood_pressure' => strip_tags($this->request->get('blood_pressure')),
            'body_temperature' => strip_tags($this->request->get('body_temperature')),
            'medical_checkup' => $listMedicalCheckup,
            'symptoms' => strip_tags($this->request->get('symptoms'))
        ];

        $appointmentLogic = new UserAppointmentLogic();
        $getResult = $appointmentLogic->appointmentFillForm($saveFormPatient, $user->id, $id);
        if ($getResult != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function downloadDiagnosis($id, $filename)
    {
        $data = AppointmentDoctor::where('id', $id)->first();
        if ($data && $data->status == 80) {
            $generateLogic = new generateLogic();
            $generateLogic->generatePdfDiagnosis($data, $filename);
            exit;
        }
    }

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $appointmentLogic = new UserAppointmentLogic();
        $getAppointmentResult = $appointmentLogic->appointmentMeeting($user->id, $id);
        if ($getAppointmentResult['success'] != 80) {
            return response()->json([
                'success' => 0,
                'message' => [$getAppointmentResult['message']] ?? [''],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getAppointmentResult['data'],
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

//        var_dump($getAppointmentResult['data']); die();
//        $getAppointment = $getAppointmentResult['data']['data'];
//
//        $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor.user_id AS doctor_user_id, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
//            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
//            ->join('users', 'users.id', '=', 'doctor.user_id')
//            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
//            ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
//            ->where('appointment_doctor.user_id', $user->id)
//            ->where('appointment_doctor.id', $id)
//            ->first();
//
//        if (!$data) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//        else if ($data->status != 3) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Janji Temu Dokter Belum di Setujui'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//        else if ($data->online_meeting == 80) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Meeting sudah selesai'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//
//        $getService = Service::where('id', $data->service_id)->first();
//        if (!$getService) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//        elseif ($getService->type != 1) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//
//        $getPatient = Users::where('id', $data->doctor_user_id)->first();
//        $getFcmTokenPatient = [];
//        if ($getPatient) {
//            $getFcmTokenPatient = $getPatient->getDeviceToken()->pluck('token')->toArray();
//        }
//
//        $getTimeMeeting = intval($this->setting['time-online-meeting']) ?? 30;
//
//        if ($data->time_start_meeting == null) {
//            $data->time_start_meeting = date('Y-m-d H:i:s');
//            $data->save();
//            $dateStopMeeting = date('Y-m-d');
//            $timeStopMeeting = date('H:i:s', strtotime("+$getTimeMeeting minutes"));
//        }
//        else {
//            $dateStopMeeting = date('Y-m-d');
//            $timeStopMeeting = date('H:i:s', strtotime($data->time_start_meeting) + (60*$getTimeMeeting));
//        }
//
//        $getVideo = json_decode($data->video_link, true);
//        $agoraId = $getVideo['id'] ?? '';
//        $agoraChannel = $getVideo['channel'] ?? '';
//        $agoraUid = $getVideo['uid_pasien'] ?? '';
//        $agoraToken = $getVideo['token_pasien'] ?? '';
//
//        return response()->json([
//            'success' => 1,
//            'data' => [
//                'info' => $data,
//                'date' => $data->date,
//                'time_server' => date('H:i:s'),
//                'date_stop_meeting' => $dateStopMeeting,
//                'time_stop_meeting' => $timeStopMeeting,
//                'time_start' => $data->time_start,
//                'time_end' => $data->time_end,
//                'video_app_id' => $agoraId,
//                'video_channel' => $agoraChannel,
//                'video_uid' => $agoraUid,
//                'video_token' => $agoraToken,
//                'fcm_token' => $getFcmTokenPatient
//            ],
//            'message' => ['Sukses'],
//            'token' => $this->request->attributes->get('_refresh_token'),
//        ]);
    }

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $appointmentLogic = new UserAppointmentLogic();
        $getResult = $appointmentLogic->appointmentCancel($user->id, $id);
        if (!$getResult) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Batalkan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cart($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $userLogic = new UserLogic();
        $userLogic->userCartAppointmentDoctor($user->id, $id);

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)
            ->where('product_qty', '>', 0)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails
            ],
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function chooseCart($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $validator = Validator::make($this->request->all(), [
            'product_ids' => 'required|array',
            'qty' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getProductIds = $this->request->get('product_ids');
        $getCartQty = $this->request->get('qty');

        DB::beginTransaction();

        $getUsersCartDetails = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product_id, choose, product_qty, product_qty_checkout')
            ->join('appointment_doctor', 'appointment_doctor.id', '=', 'appointment_doctor_product.appointment_doctor_id')
            ->where('user_id', $user->id)
            ->whereIn('product_id', $getProductIds)
            ->where('appointment_doctor_product.appointment_doctor_id', $data->id)
            ->get();

        $haveProduct = 0;
        if ($getUsersCartDetails) {
            foreach ($getUsersCartDetails as $index => $getUsersCartDetail) {
                if (in_array($getUsersCartDetail->product_id, $getProductIds)) {
                    $haveProduct = 1;
                    $getUsersCartDetail->choose = 1;
                    $getUsersCartDetail->product_qty_checkout = isset($getCartQty[$index]) ? intval($getCartQty[$index]) : 0;
                }
                else {
                    $getUsersCartDetail->choose = 0;
                }
                $getUsersCartDetail->save();
            }
        }

        DB::commit();

        if ($haveProduct == 1) {
            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil Memilih Produk'],
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

    }

    public function receiver($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id, $user->phone)
        ]);
    }

    public function updateReceiver($id)
    {
        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'receiver' => 'required',
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

        $receiver = $this->request->get('receiver');
        $address = $this->request->get('address');
        $phone = $this->request->get('phone');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getInformation = [
            'receiver' => $receiver,
            'address' => $address,
            'phone' => $phone,
        ];
        $getUsersCart->detail_information = json_encode($getInformation);
        $getUsersCart->save();

        $getData = ['detail_information' => $getInformation];


        return response()->json([
            'success' => 1,
            'message' => ['Detail Informasi Berhasil Diperbarui'],
            'data' => $getData
        ]);
    }

    public function getAddress($id)
    {
        $user = $this->request->attributes->get('_user');

        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id, $user->phone)
        ]);
    }

    public function shipping($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        if ($getDetails->count() <= 0) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

        $getShipping = Shipping::where('status', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'shipping' => $getShipping
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function payment($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        $shippingId = intval($this->request->get('shipping_id'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price as price, product.unit, product_qty_checkout as qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        $subTotal = 0;
        foreach ($getDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = $getShipping->price;

        if ($getDetails->count() <= 0) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

        $getPayment = Payment::where('status', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'payment' => $getPayment,
                'cart_info' => [
                    'shipping_price' => $getShippingPrice,
                    'subtotal' => $subTotal,
                    'total' => $subTotal + $getShippingPrice
                ],
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function summary($id)
    {
        $user = $this->request->attributes->get('_user');

        $shippingId = intval($this->request->get('shipping_id'));
        $receiver_name = $this->request->get('receiver_name');
        $receiver_address = $this->request->get('receiver_address');
        $receiver_phone = $this->request->get('receiver_phone');

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price as price, product.unit, product_qty_checkout as qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.choose', '=', 1)
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        $subTotal = 0;
        foreach ($getDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = $getShipping->price;

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $receiver_name ??$getDetailsInformation['receiver'] ?? '',
                    'address' => $receiver_address ?? $getDetailsInformation['address'] ?? '',
                    'phone' => $receiver_phone ?? $getDetailsInformation['phone'] ?? '',
                    'shipping_name' => $getShipping->name,
                    'shipping_price' => $getShippingPrice,
                    'subtotal' => $subTotal,
                    'total' => $subTotal + $getShippingPrice
                ],
                'cart_details' => $getDetails
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        if ($type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu Hanya untuk pasien dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $needPhone = 0;
        $validator = Validator::make($this->request->all(), [
            'shipping_id' => 'required|numeric',
            'payment_id' => 'required|numeric',
            'receiver_name' => 'required',
            'receiver_phone' => 'required',
            'receiver_address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = intval($this->request->get('payment_id'));
        $getPayment = Payment::where('id', $paymentId)->first();
        if (!$getPayment) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $shippingId = intval($this->request->get('shipping_id'));
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $receiver_name = $this->request->get('receiver_name');
        $receiver_address = $this->request->get('receiver_address');
        $receiver_phone = $this->request->get('receiver_phone');

        $getDetailsInformation = [
            'receiver_name' => $receiver_name,
            'receiver_address' => $receiver_address,
            'receiver_phone' => $receiver_phone
        ];


        if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
            $needPhone = 1;
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
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getShippingPrice = $getShipping->price;

        $total = 0;
        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, product_qty_checkout, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->where('choose', 1)
            ->get();
        foreach ($getDetails as $list) {
            $total += $list->price;
        }

        $total += $getShippingPrice;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'shipping_id' => $shippingId,
                'user_id' => $user->id,
                'detail_info' => json_encode($getDetailsInformation),
                'type_service' => 'product_klinik',
                'service_id' => 0,
                'appointment_doctor_id' => $id
            ],
            'code' => $newCode,
            'total' => $total,
            'name' => $user->fullname
        ];

        if ($needPhone == 1) {
            $sendData['phone'] = $this->request->get('phone');
        }

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $sendData);
        if ($getPaymentInfo['success'] == 1) {

            return response()->json([
                'success' => 1,
                'data' => [
                    'payment' => 0,
                    'info' => $getPaymentInfo['info']
                ],
                'message' => ['Berhasil'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => [$getPaymentInfo['message'] ?? '-'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

    }

}
