<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\agoraLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class DoctorAppointmentController extends Controller
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
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $s = strip_tags($this->request->get('s'));
        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $dateNow = date('Y-m-d');

        switch ($time) {
            case 2 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                        ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                        ->join('users', 'users.id', '=', 'doctor.user_id')
                        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                        ->where('doctor_id', $getDoctor->id)
                        ->whereIn('appointment_doctor.status', [1]);
                break;
            case 3 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                        ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                        ->join('users', 'users.id', '=', 'doctor.user_id')
                        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                        ->where('doctor_id', $getDoctor->id)
                        ->where('appointment_doctor.status', '=', 80);
                break;
            case 4 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                        ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                        ->join('users', 'users.id', '=', 'doctor.user_id')
                        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                        ->where('doctor_id', $getDoctor->id)
                        ->where('appointment_doctor.status', 2);
                break;
            default: $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                        ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                        ->join('users', 'users.id', '=', 'doctor.user_id')
                        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                        ->where('doctor_id', $getDoctor->id)
                        //->where('appointment_doctor.date', '>=', $dateNow)
                        ->whereIn('appointment_doctor.status', [3,4]);
                break;
        }
        if (strlen($s) > 0) {
            $data = $data->where('patient_name', 'LIKE', "%$s%");
        }
        $data = $data->orderBy('id','DESC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'default_image' => asset('assets/cms/images/no-img.png'),
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor_id', $getDoctor->id)
            ->where('appointment_doctor.id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $formPatient = json_decode($data->form_patient, true);
        $doctorPrescription = json_decode($data->doctor_prescription, true);

        $dataProducts = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product_id, product_name,
            product_qty, product_qty_checkout, product_price, dose, type_dose, period, note, choose,
            CONCAT("'.env('OSS_URL').'/'.'", product.image) AS product_image_full')
            ->leftJoin('product', 'product.id', '=', 'appointment_doctor_product.product_id')->where('appointment_doctor_id', $id)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'products' => $dataProducts,
                'form_patient' => $formPatient,
                'doctor_prescription' => $doctorPrescription
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $dateNow = strtotime(date('Y-m-d'));

        $data = AppointmentDoctor::selectRaw('appointment_doctor.*, users.id AS doctor_user_id,doctor_category.name, users.image,
                CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor_id', $getDoctor->id)
            ->where('appointment_doctor.id', $id)
            ->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($data->status != 3) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Belum di Setujui'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        //else if(strtotime($data->date) != $dateNow){
        //    return response()->json([
        //        'success' => 0,
        //        'message' => ['Meeting belum di mulai'],
        //        'token' => $this->request->attributes->get('_refresh_token'),
        //    ], 422);
        //}

        $getService = Service::where('id', $data->service_id)->first();
        if (!$getService) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        elseif ($getService->type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->online_meeting = 2;
        $data->time_start_meeting = null;
        $agoraLogic = new agoraLogic();
        if (strlen($data->video_link) <= 10) {
            $agoraChannel = $user->id.$data->user_id.'tele'.md5($data->date.$data->time_start .$data->time_end.$data->doctor_id.$data->user_id.rand(0,100));
            $agoraUidDokter = ('1'.generateNewCode(10, 2)) * 1;
            $agoraUidPasien = ('1'.generateNewCode(10, 2)) * 1;
            $agoraTokenDokter = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDokter);
            $agoraTokenPasien = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPasien);
            $agoraId = $agoraLogic->getAppId();
            $data->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_dokter' => $agoraUidDokter,
                'uid_pasien' => $agoraUidPasien,
                'token_dokter' => $agoraTokenDokter,
                'token_pasien' => $agoraTokenPasien
            ]);
        }
        else {
            $getVideo = json_decode($data->video_link, true);
            $agoraId = $getVideo['id'] ?? '';
            $agoraChannel = $getVideo['channel'] ?? '';
            $agoraUidDokter = $getVideo['uid_dokter'] ?? '';
            $agoraUidPasien = $getVideo['uid_pasien'] ?? '';

            $agoraTokenDokter = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDokter);
            $agoraTokenPasien = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPasien);

            $data->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_dokter' => $agoraUidDokter,
                'uid_pasien' => $agoraUidPasien,
                'token_dokter' => $agoraTokenDokter,
                'token_pasien' => $agoraTokenPasien
            ]);

        }

        $data->save();

        $getPatient = Users::where('id', $data->user_id)->first();
        $getFcmTokenPatient = [];
        if ($getPatient) {
            $getFcmTokenPatient = $getPatient->getDeviceToken()->pluck('token')->toArray();
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'info' => $data,
                'date' => $data->date,
                'time_server' => date('H:i:s'),
                'time_start' => $data->time_start,
                'time_end' => $data->time_end,
                'video_app_id' => $agoraId,
                'video_channel' => $agoraChannel,
                'video_uid' => $agoraUidDokter,
                'video_token' => $agoraTokenDokter,
                'fcm_token' => $getFcmTokenPatient,
            ],
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function finishMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($data->status != 3) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Belum di Setujui'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getService = Service::where('id', $data->service_id)->first();
        if (!$getService) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        elseif ($getService->type != 1) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Bukan untuk meeting'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->online_meeting = 80;
        //$data->status = 80;
        $data->save();

        $getPatient = Users::where('id', $data->user_id)->first();
        $getFcmTokenPatient = [];
        if ($getPatient) {
            $getFcmTokenPatient = $getPatient->getDeviceToken()->pluck('token')->toArray();
        }

        return response()->json([
            'success' => 1,
            'data' => $data,
            'fcm_token' => $getFcmTokenPatient,
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function approveMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [1,2])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getService = Service::where('id', $data->service_id)->first();
        if ($getService && $getService->type == 1) {
            $data->video_link = '';
            $data->online_meeting = 1;
            $data->status = 3;
        }
        else {
            $data->status = 4;
        }

        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Approve'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cancelCallMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [3])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->online_meeting = 1;
        $data->attempted += 1;
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Tutup'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function stopMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [1,2])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->status = 99;
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Batalkan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function reschedule($id){

        $user = $this->request->attributes->get('_user');

        $message = strip_tags($this->request->get('message'));

        $getDoctor = Doctor::where('user_id', $user->id)->first();

        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

       $data = AppointmentDoctor::whereIn('status', [1,2])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
       if (!$data) {
           return response()->json([
               'success' => 0,
               'message' => ['Janji Temu Dokter Tidak Ditemukan'],
               'token' => $this->request->attributes->get('_refresh_token'),
           ], 404);
       }

        $data->status = 2;
        $data->online_meeting = 0;
        $data->attempted = 0;
        $data->message = $message;
        $data->save();

       return response()->json([
           'success' => 1,
           'message' => ['Sukses Reschedule'],
           'token' => $this->request->attributes->get('_refresh_token'),
       ]);

    }

    public function doctorMedicine($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [3,4])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::where('klinik_id', $user->klinik_id);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('name','ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorDiagnosis($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();
        if (!$getDoctor) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $validator = Validator::make($this->request->all(), [
            'diagnosis' => 'required',
            'treatment' => 'required',
            'body_height' => '',
            'body_weight' => '',
            'blood_pressure' => '',
            'body_temperature' => '',
            'complaint' => '',
            'product_ids' => 'array'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [3,4])->where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDoctorPrescription = $this->request->get('doctor_prescription');
        $listDoctorPrescription = [];
        foreach ($getDoctorPrescription as $listImage) {
            $image = base64_to_jpeg($listImage);
            $destinationPath = 'synapsaapps/users/'.$data->user_id.'/doctor';
            $set_file_name = date('Ymd').'_'.md5('doctor_prescription'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name;
                $listDoctorPrescription[] = $getImage;
            }
        }

        DB::beginTransaction();

        $getListProduct = $this->request->get('product_ids');
        $getQty = $this->request->get('qty');
        $getDose = $this->request->get('dose');
        $getTypeDose = $this->request->get('type_dose');
        $getPeriod = $this->request->get('period');
        $getNote = $this->request->get('note');

        $getListQty = [];
        if($getQty) {
            foreach ($getQty as $index => $list) {
                $getListQty[] = $list;
            }
        }

        $getListDose = [];
        if ($getDose){
            foreach ($getDose as $index => $list){
                $getListDose[] = $list;
            }
        }

        $getListPeriod = [];
        if ($getPeriod){
            foreach ($getPeriod as $index => $list){
                $getListPeriod[] = $list;
            }
        }

        $getListTypeDose = [];
        if ($getTypeDose){
            foreach ($getTypeDose as $index => $list){
                $getListTypeDose[] = $list;
            }
        }

        $getListProductId = [];
        if ($getListProduct) {
            foreach ($getListProduct as $index => $list) {
                $getListProductId[] = $list;
            }
        }

        $getListNote = [];
        if ($getNote){
            foreach ($getNote as $index => $list){
                $getListNote[] = $list;
            }
        }

        $getProducts = Product::whereIn('id', $getListProductId)->get();
        foreach ($getProducts as $index => $list) {
            $getQty = intval($getListQty[$index]) > 0 ? intval($getListQty[$index]) : 1;
            $getDose = isset($getListDose[$index]) ? strip_tags($getListDose[$index]) : '';
            $getTypeDose = isset($getListTypeDose[$index]) ? intval($getListTypeDose[$index]) : 1;
            $getPeriod = isset($getListPeriod[$index]) ? $getListPeriod[$index] : '';
            $getNote = isset($getListNote[$index]) ? $getListNote[$index] : '';

            AppointmentDoctorProduct::create([
                'appointment_doctor_id' => $id,
                'product_id' => $list->id,
                'product_name' => $list->name,
                'product_qty' => $getQty,
                'product_price' => $list->price,
                'dose' => $getDose,
                'type_dose' => $getTypeDose,
                'period' => $getPeriod,
                'note' => $getNote,
                'choose' => 0,
                'status' => 1
            ]);
        }

        $getFormPatient = json_decode($data->form_patient, true);

        $saveFormPatient = [
            'body_height' => strip_tags($this->request->get('body_height')) ?? $getFormPatient['body_height'],
            'body_weight' => strip_tags($this->request->get('body_weight')) ?? $getFormPatient['body_weight'],
            'blood_pressure' => strip_tags($this->request->get('blood_pressure')) ?? $getFormPatient['blood_pressure'],
            'body_temperature' => strip_tags($this->request->get('body_temperature')) ?? $getFormPatient['body_temperature'],
            'medical_checkup' => $getFormPatient['medical_checkup'] ?? [],
            'complaint' => strip_tags($this->request->get('complaint')) ?? $getFormPatient['complaint']
        ];

        $data->diagnosis = strip_tags($this->request->get('diagnosis'));
        $data->treatment = strip_tags($this->request->get('treatment'));
        $data->doctor_prescription = json_encode($listDoctorPrescription);
        $data->form_patient = json_encode($saveFormPatient);
        $data->status = 80;
        $data->save();

        DB::commit();

        $getPatient = Users::where('id', $data->user_id)->first();
        $getFcmTokenPatient = [];
        if ($getPatient) {
            $getFcmTokenPatient = $getPatient->getDeviceToken()->pluck('token')->toArray();
        }

        $getDoctor = Users::where('id', $user->id)->first();
        $getFcmTokenDoctor = [];
        if ($getPatient) {
            $getFcmTokenDoctor = $getDoctor->getDeviceToken()->pluck('token')->toArray();
        }

        return response()->json([
            'success' => 1,
            'data' => $data,
            'doctor_name' => $user->fullname,
            'fcm_token_patient' => $getFcmTokenPatient,
            'fcm_token_doctor' => $getFcmTokenDoctor,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
