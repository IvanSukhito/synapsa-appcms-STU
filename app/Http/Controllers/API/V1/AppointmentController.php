<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\AppointmentNurse;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
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

        $dateNow = date('Y-m-d');

        switch ($time) {
            case 2 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                        ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                        ->join('users', 'users.id', '=', 'doctor.user_id')
                        ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                        ->where('appointment_doctor.user_id', $user->id)
                       // ->where('appointment_doctor.date', '=', $dateNow)
                        ->whereIn('appointment_doctor.status', [1,2])
                        ->union(
                            AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full')
                                ->join('appointment_lab_details', function($join){
                                    $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                        ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                                })
                                ->join('lab', function($join){
                                    $join->on('lab.id','=','appointment_lab_details.lab_id')
                                        ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                                })
                                ->where('appointment_lab.user_id', $user->id)
                                //->where('appointment_lab.date', '=', $dateNow)
                                ->whereIn('appointment_lab.status', [1,2])
                        )
                        ->union(
                            AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, 0 as time_start, 0 as time_end, appointment_nurse.status, shift_qty as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full')
                                ->where('appointment_nurse.user_id', $user->id)
                                //->where('appointment_lab.date', '=', $dateNow)
                                ->whereIn('appointment_nurse.status', [1,2])
                        );
            break;
            case 3 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->where('appointment_doctor.user_id', $user->id)
                ->where('appointment_doctor.status', '=', 80)
                ->union(
                    AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full')
                        ->join('appointment_lab_details', function($join){
                            $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                        })
                        ->join('lab', function($join){
                            $join->on('lab.id','=','appointment_lab_details.lab_id')
                                ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                        })
                        ->where('appointment_lab.user_id', $user->id)
                        ->where('appointment_lab.status', '=', 80)
                )->union(
                    AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, 0 as time_start, 0 as time_end, appointment_nurse.status, shift_qty as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full')
                        ->where('appointment_nurse.user_id', $user->id)
                        ->where('appointment_nurse.status', '=',80)
                );
                break;
            case 4 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->where('appointment_doctor.user_id', $user->id)
                ->where('appointment_doctor.status', '>=', 2)
                ->union(
                    AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full')
                        ->join('appointment_lab_details', function($join){
                            $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                        })
                        ->join('lab', function($join){
                            $join->on('lab.id','=','appointment_lab_details.lab_id')
                                ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                        })
                        ->where('appointment_lab.user_id', $user->id)
                        ->where('appointment_lab.status', '>=', 2)
                )->union(
                    AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, 0 as time_start, 0 as time_end, appointment_nurse.status, shift_qty as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full')
                        ->where('appointment_nurse.user_id', $user->id)
                        //->where('appointment_lab.date', '=', $dateNow)
                        ->where('appointment_nurse.status', '>=', 2)
                );
                break;
           default:
               $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                   ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                   ->join('users', 'users.id', '=', 'doctor.user_id')
                   ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                   ->where('appointment_doctor.user_id', $user->id)
                   ->where('appointment_doctor.date', '>=', $dateNow)
                   ->whereIn('appointment_doctor.status', [3,4])
                   ->union(
                       AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full')
                           ->join('appointment_lab_details', function($join){
                               $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                   ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                           })
                           ->join('lab', function($join){
                               $join->on('lab.id','=','appointment_lab_details.lab_id')
                                   ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                           })
                           ->where('appointment_lab.user_id', $user->id)
                           ->where('appointment_lab.date', '>=', $dateNow)
                           ->whereIn('appointment_lab.status', [3,4])
                   )
                   ->union(
                       AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, appointment_nurse.status, 0 as time_start, 0 as time_end, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full, appointment_nurse.shift_qty as shift_qty')
                           ->where('appointment_nurse.user_id', $user->id)
                           ->where('appointment_nurse.date', '>=', $dateNow)
                           ->whereIn('appointment_nurse.status', [3,4])
                   );
               break;
       }

        $data = $data->orderBy('date','DESC')->orderBy('time_start','ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $type = intval($this->request->get('type'));

        if($type == 1)
        {
            $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.user_id', $user->id)
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

            $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
                ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
                ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

            return response()->json([
                'success' => 1,
                'data' => [
                    'data' => $data,
                    'product' => $getDetails,
                    'form_patient' => $formPatient,
                    'doctor_prescription' => $doctorPrescription,
                    'address' => $this->getUserAddress($user->id),
                    'phone'  => $this->getUserAddress($user->id)['phone']
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
        elseif($type == 2)
        {
            $data = AppointmentLab::selectRaw('appointment_lab.*, lab.name as lab_name')
                    ->join('appointment_lab_details', function($join){
                        $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                            ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                    })
                    ->join('lab', function($join){
                        $join->on('lab.id','=','appointment_lab_details.lab_id')
                            ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                    })
                    ->where('user_id',$user->id)
                    ->where('appointment_lab.id', $id)
                    ->first();

            if(!$data){
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Lab Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            $getDetails = $data->getAppointmentLabDetails()->selectRaw('appointment_lab_details.*,
                    lab.image, CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full')
                    ->join('lab','lab.id','=','appointment_lab_details.lab_id')
                    ->get();

            return response()->json([
                'success' => 1,
                'data' => [
                    'data' => $data,
                    'lab_product' => $getDetails,
                    'address' => $this->getUserAddress($user->id),
                    'phone'  => $this->getUserAddress($user->id)['phone'],
                 ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        elseif($type == 3)
        {
            $data = AppointmentNurse::selectRaw('appointment_nurse.*')
                ->where('user_id',$user->id)
                ->where('appointment_nurse.id', $id)
                ->first();

            if(!$data){
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Perawat Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
            return response()->json([
                'success' => 1,
                'data' => [
                    'data' => $data,
                    'address' => $this->getUserAddress($user->id),
                    'phone'  => $this->getUserAddress($user->id)['phone'],
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        return response()->json([
            'success' => 0,
            'message' => ['Type Janji Temu Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

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
            'keluhan' => 'required',
            'medical_checkup' => ''
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        if (in_array($data->status, [1,2])) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Belum di setujui'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
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
            'keluhan' => strip_tags($this->request->get('keluhan'))
        ];

        $data->form_patient = json_encode($saveFormPatient);
        $data->status = 3;
        $data->save();

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function reschedule($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));
        $message = strip_tags($this->request->get('message'));

        $getDate = strtotime($this->request->get('date')) > 0 ?
            date('Y-m-d', strtotime($this->request->get('date'))) :
            date('Y-m-d', strtotime("+1 day"));

        $dateNow = date('Y-m-d');

        if($getDate <= $dateNow){
            return response()->json([
                'success' => 0,
                'message' => ['Pilih Jadwal Harus Lebih Dari Hari Ini'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        if ($type == 1) {
            $getAppointment = AppointmentDoctor::where('user_id', $user->id)->where('id', $id)->first();
            if (!$getAppointment) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
            else if (!in_array($getAppointment->status, [1,2])) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Dokter tidak bisa di ganti'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            $serviceId = $getAppointment->service_id;
            $doctorId = $getAppointment->doctor_id;
            $getService = Service::where('id', $serviceId)->first();
            $data = $this->getDoctorInfo($doctorId, $serviceId);
            if (!$data) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Doktor Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }

            $getSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
                ->where('date_available', '=', $getDate)
                ->get();

            $getList = get_list_type_service();

            $getAppointment->status = 2;
            $getAppointment->message = $message;
            $getAppointment->save();

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                    'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'date' => $getDate,
                    'schedule' => $getSchedule,
                    'doctor' => $data,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
        else if ($type == 2) {
            $getAppointment = AppointmentLab::where('user_id', $user->id)->where('id', $id)->first();
            if (!$getAppointment) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Lab Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
            else if (!in_array($getAppointment->status, [1,2])) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Lab tidak bisa di ganti'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            $serviceId = $getAppointment->service_id;
            $getService = Service::where('id', $serviceId)->first();

            $getSchedule = LabSchedule::where('service_id', '=', $serviceId)
                ->where('date_available', '=', $getDate)
                ->get();

            $getList = get_list_type_service();

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                    'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'date' => $getDate,
                    'schedule' => $getSchedule,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        return response()->json([
            'success' => 0,
            'message' => ['Type Janji Temu Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

    }

    public function updateSchedule($id)
    {
        $user = $this->request->attributes->get('_user');
        $type = intval($this->request->get('type'));

        $validator = Validator::make($this->request->all(), [
            'schedule_id' => 'numeric|required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        if ($type == 1) {
            $getAppointment = AppointmentDoctor::where('user_id', $user->id)->where('id', $id)->first();
            if (!$getAppointment) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
            else if (!in_array($getAppointment->status, [1,2])) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Dokter tidak bisa di ganti'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            $serviceId = $getAppointment->service_id;
            $doctorId = $getAppointment->doctor_id;

            $scheduleId = $this->request->get('schedule_id');
            $getSchedule = DoctorSchedule::where('id', $scheduleId)
                ->where('doctor_id', $doctorId)
                ->where('service_id', $serviceId)->where('book', 80)
                ->where('date_available', '>=', date('Y-m-d'))
                ->first();
            if (!$getSchedule) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Jadwal Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            DB::beginTransaction();

            $oldSchedule = $getAppointment->schedule_id;
            DoctorSchedule::where('id', $oldSchedule)->update([
                'book' => 80
            ]);

            $getSchedule->book = 99;
            $getSchedule->save();

            $getAppointment->schedule_id = $getSchedule->id;
            $getAppointment->date = $getSchedule->date_available;
            $getAppointment->time_start = $getSchedule->time_start;
            $getAppointment->time_end = $getSchedule->time_end;
            $getAppointment->status = 1;
            $getAppointment->save();

            DB::commit();

            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil menganti schedule'],
            ]);
        }
        else if ($type == 2) {
            $getAppointment = AppointmentLab::where('user_id', $user->id)->where('id', $id)->first();
            if (!$getAppointment) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Lab Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 404);
            }
            else if ($getAppointment->status != 1) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Janji Temu Lab tidak bisa di ganti'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            $serviceId = $getAppointment->service_id;

            $scheduleId = $this->request->get('schedule_id');
            $getSchedule = LabSchedule::where('id', $scheduleId)
                ->where('service_id', $serviceId)->where('book', 80)
                ->where('date_available', '>=', date('Y-m-d'))->first();
            if (!$getSchedule) {
                return response()->json([
                    'success' => 0,
                    'message' => ['Jadwal Tidak Ditemukan'],
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }

            DB::beginTransaction();

            $getAppointment->schedule_id = $getSchedule->id;
            $getAppointment->date = $getSchedule->date_available;
            $getAppointment->time_start = $getSchedule->time_start;
            $getAppointment->time_end = $getSchedule->time_end;
            $getAppointment->save();

            DB::commit();

            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil menganti schedule'],
            ]);
        }

        return response()->json([
            'success' => 0,
            'message' => ['Type Janji Temu Tidak Ditemukan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ], 404);

    }

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.user_id', $user->id)
            ->where('appointment_doctor.id', $id)
            ->first();

        $dateNow = strtotime(date('Y-m-d'));
        $timeNow = strtotime("+5 minutes");

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
        else if ($data->online_meeting == 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Meeting sudah selesai'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if (in_array($data->online_meeting, [0,1])) {
            return response()->json([
                'success' => 0,
                'message' => ['Meeting belum di mulai'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if(strtotime($data->date) != $dateNow){
            return response()->json([
                'success' => 0,
                'message' => ['Hari Meeting belum di mulai'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else if($timeNow >= strtotime($data->time_start) && strtotime($data->time_end) <= strtotime("now")){
            return response()->json([
                'success' => 0,
                'message' => ['Waktu Meeting belum di mulai'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
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

        return response()->json([
            'success' => 1,
            'data' => [
                'info' => $data,
                'date' => $data->date,
                'time_server' => date('H:i:s'),
                'time_start' => $data->time_start,
                'time_end' => $data->time_end,
                'app_id' => env('AGORA_APP_ID'),
                'channel' => $data->video_link,
                'fcm_token' => $user->getDeviceToken->first()->token ?? ''
            ],
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::whereIn('status', [1,2,80])->where('user_id', $user->id)->where('id', $id)->first();
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
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

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
            'cart_ids' => 'required|array',
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

        $getCartIds = $this->request->get('cart_ids');

        DB::beginTransaction();

        $getUsersCartDetails = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product_id, choose')
            ->join('appointment_doctor', 'appointment_doctor.id', '=', 'appointment_doctor_product.appointment_doctor_id')
            ->where('user_id', $user->id)->get();

        $haveProduct = 0;
        if ($getUsersCartDetails) {
            foreach ($getUsersCartDetails as $getUsersCartDetail) {
                if (in_array($getUsersCartDetail->product_id, $getCartIds)) {
                    $haveProduct = 1;
                    $getUsersCartDetail->choose = 1;
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


        if ($data) {
            $getReceiver = $user->fullname ?? '';
            $getAddress = $user->address ?? '';
            $getPhone = $user->phone ?? '';
        }

        return response()->json([
            'success' => 1,
            'data' => [
                [
                    'receiver' => $getReceiver ?? '',
                    'address' => $getAddress ?? '',
                    'phone' => $getPhone ?? '',
                ]
            ]
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

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getUsersAddress ? $getUsersAddress->address_name : '';
        $getAddress = $getUsersAddress ? $getUsersAddress->address : '';
        $getCity = $getUsersAddress ? $getUsersAddress->city_id : '';
        $getDistrict = $getUsersAddress ? $getUsersAddress->district_id : '';
        $getSubDistrict = $getUsersAddress ? $getUsersAddress->sub_district_id : '';
        $getZipCode = $getUsersAddress ? $getUsersAddress->zip_code : '';

        return response()->json([
            'success' => 1,
            'data' => [
                'address_name' => $getAddressName ?? $user->address,
                'address' => $getAddress ?? $user->address_detail,
                'city_id' => $getCity ?? $user->city_id,
                'district_id' => $getDistrict ?? $user->district_id,
                'sub_district_id' => $getSubDistrict ?? $user->sub_district_id,
                'zip_code' => $getZipCode ?? $user->zip_code,
            ]
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

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_   doctor_product.product_qty, appointment_doctor_product.choose')
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

        $getPayment = Payment::where('status', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'payment' => $getPayment
            ],
            'message' => ['Berhasil'],
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

        $getShippingPrice = 15000;

        $total = 0;
        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
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

    private function getUserAddress($userId)
    {
        $getUsersAddress = UsersAddress::where('user_id', $userId)->first();
        $user = $this->request->attributes->get('_user');

        $getAddressName = $getUsersAddress->address_name ??$user->address ?? '';
        $getAddress = $getUsersAddress->address ?? $user->address_detail ?? '';
        $getCity = $getUsersAddress->city_id ?? '';
        $getCityName = $getUsersAddress->city_name ?? '';
        $getDistrict = $getUsersAddress->district_id ?? '';
        $getDistrictName = $getUsersAddress->district_name ?? '';
        $getSubDistrict = $getUsersAddress->sub_district_id ?? '';
        $getSubDistrictName = $getUsersAddress->sub_district_name ?? '';
        $getZipCode = $getUsersAddress->zip_code ?? '';
        $getPhone = $user->phone ?? '';

        return [
            'address_name' => $getAddressName,
            'address' => $getAddress,
            'city_id' => $getCity,
            'city_name' => $getCityName,
            'district_id' => $getDistrict,
            'district_name' => $getDistrictName,
            'sub_district_id' => $getSubDistrict,
            'sub_district_name' => $getSubDistrictName,
            'zip_code' => $getZipCode,
            'phone' => $getPhone
        ];

    }

    private function getDoctorInfo($doctorId, $serviceId)
    {
        return Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor_service.service_id', '=', $serviceId)
            ->where('doctor.id', '=', $doctorId)
            ->where('users.doctor','=', 1)->first();
    }


}
