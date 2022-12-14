<?php

namespace App\Codes\Logic;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DoctorLogic
{
    public function __construct()
    {
    }

    /**
     * @param null $serviceId
     * @param null $subServiceId
     * @return array
     */
    public function getListService($serviceId = null, $subServiceId = null): array
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $getServiceDoctor = isset($setting['service-doctor']) ? json_decode($setting['service-doctor'], true) : [];
        if (count($getServiceDoctor) > 0) {
            $service = Service::whereIn('id', $getServiceDoctor)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }

        $result = [];
        $subService = [];
        $firstServiceId = 0;
        $firstServiceName = '';
        $firstServiceType = 0;
        $setServiceId = 0;
        $setServiceName = '';
        $setServiceType = 0;

        $setSubServiceId = 0;
        $setSubServiceName = '';

        foreach ($service as $index => $list) {
            $active = 0;
            if ($index == 0) {
                $firstServiceId = $list->id;
                $firstServiceName = $list->name;
                $firstServiceType = $list->type;
            }

            if ($list->id == $serviceId) {
                $active = 1;
                $setServiceId = $list->id;
                $setServiceName = $list->name;
                $setServiceType = $list->type;
            }

            $result[] = [
                'id' => $list->id,
                'name' => $list->name,
                'type' => $list->type,
                'type_nice' => $list->type_nice,
                'active' => $active
            ];

        }

        if ($setServiceId <= 0) {
            $setServiceId = $firstServiceId;
            $setServiceName = $firstServiceName;
            $setServiceType = $firstServiceType;
        }

        if ($setServiceType == 1) {
            $subService = get_list_sub_service();

            $subServiceId = intval($subServiceId);
            $getList = get_list_sub_service2();
            if (isset($getList[$subServiceId])) {
                $setSubServiceId = $subServiceId;
                $setSubServiceName = $getList[$subServiceId];
            }
        }

        return [
            'data' => $result,
            'sub_service' => $subService,
            'getServiceId' => $setServiceId,
            'getServiceName' => $setServiceName,
            'getSubServiceId' => $setSubServiceId,
            'getSubServiceName' => $setSubServiceName
        ];

    }

    /**
     * @param null $categoryId
     * @return array
     */
    public function getListCategory($categoryId = null): array
    {
        $category = Cache::remember('doctor_category', env('SESSION_LIFETIME'), function () {
            return DoctorCategory::orderBy('orders', 'ASC')->get();
        });

        $result = [];
        $firstCategoryId = 0;
        $firstCategoryName = '';
        $setCategoryId = 0;
        $setCategoryName = '';
        foreach ($category as $index => $list) {
            $active = 0;
            if ($index == 0) {
                $firstCategoryId = $list->id;
                $firstCategoryName = $list->name;
            }

            if ($list->id == $categoryId) {
                $active = 1;
                $setCategoryId = $list->id;
                $setCategoryName = $list->name;
            }

            $result[] = [
                'id' => $list->id,
                'name' => $list->name,
                'icon_img' => $list->icon_img,
                'icon_img_full' => $list->icon_img_full,
                'active' => $active
            ];

        }

        if ($setCategoryId <= 0) {
            $setCategoryId = $firstCategoryId;
            $setCategoryName = $firstCategoryName;
        }

        return [
            'data' => $result,
            'getCategoryId' => $setCategoryId,
            'getCategoryName' => $setCategoryName
        ];
    }

    /**
     * @param $clinicId
     * @param int $serviceId
     * @param int $categoryId
     * @param null $search
     * @param int $getLimit
     * @return mixed
     */
    public function doctorList($clinicId, int $serviceId = 0, int $categoryId = 0, $search = null, int $getLimit = 5)
    {
        if ($serviceId) {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
                ->where('doctor_service.service_id', '=', $serviceId)
                ->where('users.klinik_id', '=', $clinicId)
                ->where('users.doctor','=', 1);
        }
        else {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, 0 AS price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->where('users.klinik_id', '=', $clinicId)
                ->where('users.doctor','=', 1);
        }

        if ($categoryId > 0) {
            $getData = $getData->where('doctor.doctor_category_id','=', $categoryId);
        }
        if (strlen($search) > 0) {
            $getData = $getData->where('users.fullname', 'LIKE', "%$search%");
        }

        return $getData->orderBy('users.fullname', 'ASC')->paginate($getLimit);

    }

    /**
     * @param $userId
     * @return false|mixed
     */
    public function checkDoctor($userId)
    {
        $getDoctor = Doctor::where('user_id', '=', $userId)->first();
        if ($getDoctor) {
            return $getDoctor;
        }
        return false;
    }

    /**
     * @param $clinicId
     * @param $doctorId
     * @param null $serviceId
     * @return mixed
     */
    public function doctorInfo($clinicId, $doctorId, $serviceId = null)
    {
        if ($serviceId) {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
                ->where('doctor_service.service_id', '=', $serviceId)
                ->where('doctor.id', '=', $doctorId)
                ->where('users.klinik_id', '=', $clinicId)
                ->where('users.doctor','=', 1);
        }
        else {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, 0 AS price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->where('doctor.id', '=', $doctorId)
                ->where('users.klinik_id', '=', $clinicId)
                ->where('users.doctor','=', 1);
        }

        return $getData->first();

    }

    /**
     * @param $doctorId
     * @param $serviceId
     * @param $date
     * @return array
     */
    public function scheduleDoctorList($doctorId, $serviceId, $date): array
    {
        $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
            ->where('date_available', '=', $date)
            ->get();
        if ($getDoctorSchedule->count() <= 0) {
            $getWeekday = intval(date('w', strtotime($date)));
            if ($getWeekday > 0) {
                $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
                    ->where('weekday', '=', $getWeekday)->get();
            }
            else {
                $getDoctorSchedule = [];
            }
        }

        $getList = get_list_book();
        $getAppointmentDoctor = AppointmentDoctor::where('doctor_id', '=', $doctorId)->where('date', '=', $date)->where('status', '<', 90)->get();
        $temp = [];
        foreach ($getAppointmentDoctor as $list) {
            $temp[$list->time_start] = 99;
        }
        $getAppointmentDoctor = $temp;

        $result = [];
        if ($getDoctorSchedule) {
            foreach ($getDoctorSchedule as $list) {
                $getBook = $getAppointmentDoctor[$list->time_start] ?? 80;
                $result[] = [
                    'id' => $list->id,
                    'doctor_id' => $doctorId,
                    'service_id' => $serviceId,
                    'date_available' => $date,
                    'time_start' => $list->time_start,
                    'time_end' => $list->time_end,
                    'type' => $list->type,
                    'weekday' => $list->weekday,
                    'book' => $getBook,
                    'book_nice' => $getList[$getBook] ?? '-'
                ];
            }
        }

        return $result;

    }

    /**
     * @param $scheduleId
     * @param null $date
     * @param int $userId
     * @param int $raw
     * @return array|int|int[]
     */
    public function scheduleCheck($scheduleId, $date = null, int $userId = 0, int $raw = 0)
    {
        $getSchedule = DoctorSchedule::where('id', '=', $scheduleId)->first();
        if (!$getSchedule) {
            if ($raw == 1) {
                return [
                    'success' => 90
                ];
            }
            return 90;
        }

        $doctorId = $getSchedule->doctor_id;
        $timeStart = $getSchedule->time_start;

        if ($getSchedule->type == 1) {
            $getWeekday = intval(date('w', strtotime($date)));
            if($getWeekday != intval($getSchedule->weekday)) {
                if ($raw == 1) {
                    return [
                        'success' => 93
                    ];
                }
                return 93;
            }
            $getSchedule->date_available = $date;
        }
        else {
            $date = $getSchedule->date_available;
        }

        $getAppointmentDoctor = AppointmentDoctor::where('doctor_id', '=', $doctorId)->where('date', '=', $date)
            ->where('time_start', '=', $timeStart)->where('status', '<', 90)->first();
        if ($getAppointmentDoctor) {
            if ($getAppointmentDoctor->user_id == $userId) {
                if ($raw == 1) {
                    return [
                        'success' => 92
                    ];
                }
                return 92;
            }

            if ($raw == 1) {
                return [
                    'success' => 91
                ];
            }
            return 91;
        }

        if ($raw == 1) {
            return [
                'success' => 80,
                'schedule' => $getSchedule,
                'date' => $date,
                'time' => $timeStart
            ];
        }
        else {
            return 80;
        }
    }

    /**
     * @param $doctorId
     * @param $date
     * @param $timeStart
     * @return int
     */
    public function scheduleCheckAvailable($doctorId, $date, $timeStart): int
    {
        $getAppointmentDoctor = AppointmentDoctor::where('doctor_id', '=', $doctorId)->where('date', '=', $date)
            ->where('time_start', '=', $timeStart)->where('status', '<', 90)->first();
        if ($getAppointmentDoctor) {
            return 0;
        }
        return 1;
    }

    /**
     * @param $scheduleId
     * @param $date
     * @param $getUser
     * @param $getDoctorName
     * @param $getServiceName
     * @param $getTransaction
     * @param $newCode
     * @param $extraInfo
     * @return int
     */
    public function appointmentCreate($scheduleId, $date, $getUser, $getDoctorName, $getServiceName, $getTransaction, $newCode, $extraInfo): int
    {
        $getData = $this->scheduleCheck($scheduleId, $date, $getUser->id, 1);
        if ($getData['success'] == 80) {
            $getSchedule = $getData['schedule'];
            $getDate = $getData['date'];

            AppointmentDoctor::create([
                'transaction_id' => $getTransaction->id,
                'klinik_id' => $getTransaction->klinik_id,
                'code' => $newCode,
                'schedule_id' => $getSchedule->id,
                'service_id' => $getSchedule->service_id,
                'doctor_id' => $getSchedule->doctor_id,
                'user_id' => $getTransaction->user_id,
                'type_appointment' => $getServiceName,
                'patient_name' => $getUser ? $getUser->fullname : '',
                'patient_email' => $getUser ? $getUser->email : '',
                'doctor_name' => $getDoctorName,
                'date' => $getDate,
                'time_start' => $getSchedule->time_start,
                'time_end' => $getSchedule->time_end,
                'extra_info' => json_encode($extraInfo),
                'status' => 1
            ]);

            return 1;

        }

        return 0;

    }

    /**
     * @param $transactionIds
     * @return int
     */
    public function appointmentSuccess($transactionIds): int
    {
        AppointmentDoctor::whereIn('transaction_id', $transactionIds)->update([
            'status' => 1
        ]);
        return 1;
    }

    /**
     * @param $transactionIds
     * @return int
     */
    public function appointmentReject($transactionIds): int
    {
        AppointmentDoctor::whereIn('transaction_id', $transactionIds)->update([
            'status' => 91
        ]);
        return 1;
    }

    /**
     * @param $userId
     * @param int $setFlag
     * @param null $search
     * @param int $limit
     * @return array
     */
    public function appointmentListDoctor($userId, int $setFlag = 1, $search = null, int $limit = 10): array
    {
        $getDoctor = $this->checkDoctor($userId);
        if (!$getDoctor) {
            return [
                'success' => 0,
                'message' => 'Doctor not found'
            ];
        }

        switch ($setFlag) {
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
                ->where('appointment_doctor.status', '=', 2);
                break;
            default: $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image,
                        CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->where('doctor_id', $getDoctor->id)
                ->whereIn('appointment_doctor.status', [3,4]);
                break;
        }

        if (strlen($search) > 0) {
            $data = $data->where('patient_name', 'LIKE', "%$search%");
        }

        $data = $data->orderBy('id','DESC')->paginate($limit);

        return [
            'success' => 1,
            'data' => $data
        ];
    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return array
     */
    public function appointmentInfo($appointmentId, $userId): array
    {
        $getDoctor = $this->checkDoctor($userId);
        if (!$getDoctor) {
            return [
                'success' => 90,
                'message' => 'Doctor not found'
            ];
        }

        $getAppointment = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name,
            user_doctor.id AS doctor_user_id,
            user_doctor.image, CONCAT("'.env('OSS_URL').'/'.'", user_doctor.image) AS image_full, 
            user_patient.image, CONCAT("'.env('OSS_URL').'/'.'", user_patient.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users AS user_doctor', 'user_doctor.id', '=', 'doctor.user_id')
            ->join('users AS user_patient', 'user_patient.id', '=', 'appointment_doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor_id', $getDoctor->id)
            ->where('appointment_doctor.id', $appointmentId)
            ->first();

        if ($getAppointment) {

            $formPatient = json_decode($getAppointment->form_patient, true);
            $doctorPrescription = json_decode($getAppointment->doctor_prescription, true);

            $getProducts = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product_id, product_name,
                product_qty, product_qty_checkout, product_price, dose, type_dose, period, note, choose,
                product.image, CONCAT("'.env('OSS_URL').'/'.'", product.image) AS product_image_full')
                ->leftJoin('product', 'product.id', '=', 'appointment_doctor_product.product_id')->where('appointment_doctor_id', $appointmentId)->get();

            return [
                'success' => 80,
                'data' => [
                    'data' => $getAppointment,
                    'products' => $getProducts,
                    'form_patient' => $formPatient,
                    'doctor_prescription' => $doctorPrescription
                ]

            ];
        }
        else {
            return [
                'success' => 91,
                'message' => 'Appointment not found'
            ];
        }

    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return int
     */
    public function appointmentApprove($appointmentId, $userId): int
    {
        $getDoctor = $this->checkDoctor($userId);
        if (!$getDoctor) {
            return 90;
        }

        $getAppointment = AppointmentDoctor::whereIn('status', [1,2])->where('id', '=', $appointmentId)
            ->where('doctor_id', '=', $getDoctor->id)->first();

        if ($getAppointment) {
            $getService = Service::where('id', '=', $getAppointment->service_id)->first();
            if ($getService && $getService->type == 1) {
                $getAppointment->video_link = '';
                $getAppointment->online_meeting = 1;
                $getAppointment->status = 3;
            }
            else {
                $getAppointment->status = 4;
            }
            $getAppointment->save();
            return 80;
        }
        return 91;
    }

    /**
     * @param $appointmentId
     * @param $userId
     * @param $message
     * @return int
     */
    public function appointmentRequestReSchedule($appointmentId, $userId, $message): int
    {
        $getDoctor = $this->checkDoctor($userId);
        if (!$getDoctor) {
            return 90;
        }

        $getAppointment = AppointmentDoctor::whereIn('status', [1,2])->where('id', '=', $appointmentId)
            ->where('doctor_id', '=', $getDoctor->id)->first();
        if ($getAppointment) {
            $getAppointment->status = 2;
            $getAppointment->online_meeting = 0;
            $getAppointment->attempted = 0;
            $getAppointment->message = $message;
            $getAppointment->video_link = '';
            $getAppointment->save();
            return 80;
        }
        return 91;
    }

    /**
     * @param $doctorId
     * @param $appointmentId
     * @param $date
     * @param $timeStart
     * @param $timeEnd
     * @param $getUser
     * @param $getDoctor
     * @return int
     */
    public function appointmentChange($doctorId, $appointmentId, $date, $timeStart, $timeEnd, $getUser, $getDoctor): int
    {
        $getAppointment = AppointmentDoctor::where('id', '=', $appointmentId)->where('user_id', '=', $getUser->id)
            ->where('doctor_id', '=', $getDoctor->id)->first();
        if ($getAppointment) {
            if ($this->scheduleCheckAvailable($doctorId, $date, $timeStart)) {
                $getAppointment->time_start = $timeStart;
                $getAppointment->time_end = $timeEnd;
                $getAppointment->status = $timeEnd;
                $getAppointment->save();

                return 1;
            }
        }

        return 0;

    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return array
     */
    public function checkMeeting($appointmentId, $userId): array
    {
        $getDoctor = $this->checkDoctor($userId);
        if (!$getDoctor) {
            return [
                'success' => 90,
                'message' => 'Not Doctor Account'
            ];
        }

        $doctorId = $getDoctor->id;
        $getAppointment = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name,
            user_doctor.id AS doctor_user_id,
            user_doctor.image, CONCAT("'.env('OSS_URL').'/'.'", user_doctor.image) AS image_full, 
            user_patient.image, CONCAT("'.env('OSS_URL').'/'.'", user_patient.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users AS user_doctor', 'user_doctor.id', '=', 'doctor.user_id')
            ->join('users AS user_patient', 'user_patient.id', '=', 'appointment_doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.status', '=', 3)
            ->where('doctor_id', $getDoctor->id)
            ->where('appointment_doctor.id', $appointmentId)
            ->first();
        if (!$getAppointment) {
            return [
                'success' => 91,
                'message' => 'Appointment not found'
            ];
        }
        else if ($getAppointment->online_meeting == 80) {
            return [
                'success' => 92,
                'message' => 'Appointment already finish'
            ];
        }

        $getService = Service::where('id', '=', $getAppointment->service_id)->first();
        if (!$getService) {
            return [
                'success' => 93,
                'message' => 'Service not found'
            ];
        }
        else if ($getService->type != 1) {
            return [
                'success' => 94,
                'message' => 'Service Not support'
            ];
        }

        return [
            'success' => 80,
            'data' => $getAppointment,
            'doctor_id' => $doctorId
        ];
    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return array
     */
    public function meetingCreate($appointmentId, $userId): array
    {
        $getAppointmentData = $this->checkMeeting($appointmentId, $userId);
        if ($getAppointmentData['success'] != 80) {
            return $getAppointmentData;
        }

        $getAppointment = $getAppointmentData['data'];

        $patientId = $getAppointment->user_id;
        $getUsers = Users::select('id', 'image')->whereIn('id', [$userId, $patientId])->get();
        $listImage = [];
        $getFcmTokenPatient = [];
        foreach ($getUsers as $getUser) {
            if ($getUser->id == $patientId) {
                $getFcmTokenPatient = $getUser->getDeviceToken()->pluck('token')->toArray();
            }
            $listImage[$getUser->id] = $getUser->image_full;
        }

        $dataResult = [
            'info' => $getAppointment,
            'date' => $getAppointment->date,
            'time_server' => date('H:i:s'),
            'time_start' => $getAppointment->time_start,
            'time_end' => $getAppointment->time_end,
            'patient_image' => $listImage[$getAppointment->user_id] ?? asset('assets/cms/images/no-img.png'),
            'doctor_image' => $listImage[$userId] ?? asset('assets/cms/images/no-img.png'),
            'fcm_token' => $getFcmTokenPatient
        ];

        $getExtraInfo = json_decode($getAppointment->extra_info, true);
        if (isset($getExtraInfo['sub_service_id']) && $getExtraInfo['sub_service_id'] == 2) {

            $getChat = json_decode($getAppointment->video_link, true);
            if (isset($getChat['chat_id'])) {
                $chatId = $getChat['chat_id'];
            }
            else {
                $chatId = 'chat_'.$patientId.'_'.$userId.'_'.generateNewCode(9, 2);
                $getAppointment->video_link = json_encode([
                    'chat_id' => $chatId
                ]);
            }

            $dataResult['type'] = 'Chat';
            $dataResult['chat_id'] = $chatId;

        }
        else {

            $agoraLogic = new agoraLogic();
            if (strlen($getAppointment->video_link) <= 10) {
                $agoraChannel = $getAppointment->id.$getAppointment->user_id.'tele'.md5($getAppointment->date.$getAppointment->time_start .$getAppointment->time_end.$getAppointment->doctor_id.$getAppointment->user_id.rand(0,100));
                $agoraUidDoctor = rand(1000000000, 9999999999);
                $agoraUidPatient = rand(1000000000, 9999999999);
                $agoraTokenDoctor = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDoctor);
                $agoraTokenPatient = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPatient);
                $agoraId = $agoraLogic->getAppId();

            }
            else {
                $getVideo = json_decode($getAppointment->video_link, true);
                $agoraId = $getVideo['id'] ?? '';
                $agoraChannel = $getVideo['channel'] ?? '';
                $agoraUidDoctor = $getVideo['uid_doctor'] ?? '';
                $agoraUidPatient = $getVideo['uid_patient'] ?? '';
                $agoraTokenDoctor = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDoctor);
                $agoraTokenPatient = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPatient);

            }

            $getAppointment->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_doctor' => $agoraUidDoctor,
                'uid_patient' => $agoraUidPatient,
                'token_doctor' => $agoraTokenDoctor,
                'token_patient' => $agoraTokenPatient
            ]);

            $dataResult['type'] = 'Video Call';
            $dataResult['video_app_id'] = $agoraId;
            $dataResult['video_channel'] = $agoraChannel;
            $dataResult['video_uid'] = $agoraUidDoctor;
            $dataResult['video_token'] = $agoraTokenDoctor;

        }

        $getAppointment->online_meeting = 2;
        $getAppointment->time_start_meeting = null;
        $getAppointment->save();

        return [
            'success' => 80,
            'data' => $dataResult
        ];

    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return array
     */
    public function meetingGetInfo($appointmentId, $userId): array
    {
        $getAppointmentData = $this->checkMeeting($appointmentId, $userId);
        if ($getAppointmentData['success'] != 80) {
            return $getAppointmentData;
        }

        $getAppointment = $getAppointmentData['data'];

        $doctorId = $getAppointmentData['doctor_id'];
        $patientId = $getAppointment->user_id;
        $getUsers = Users::select('id', 'image')->whereIn('id', [$userId, $patientId])->get();
        $listImage = [];
        $getFcmTokenPatient = [];
        $getFcmTokenDoctor = [];
        foreach ($getUsers as $getUser) {
            if ($getUser->id == $patientId && $type == 2) {
                $getFcmTokenPatient = $getUser->getDeviceToken()->pluck('token')->toArray();
            }
            else if($getUser->id == $doctorId && $type == 1) {
                $getFcmTokenDoctor = $getUser->getDeviceToken()->pluck('token')->toArray();
            }
            $listImage[$getUser->id] = $getUser->image_full;
        }

        $dataResult = [
            'info' => $getAppointment,
            'date' => $getAppointment->date,
            'time_server' => date('H:i:s'),
            'time_start' => $getAppointment->time_start,
            'time_end' => $getAppointment->time_end,
            'patient_image' => $listImage[$getAppointment->user_id] ?? asset('assets/cms/images/no-img.png'),
            'doctor_image' => $listImage[$userId] ?? asset('assets/cms/images/no-img.png'),
            'fcm_token' => $type == 1 ? $getFcmTokenDoctor : $getFcmTokenPatient
        ];

        $getExtraInfo = json_decode($getAppointment->extra_info, true);
        if (isset($getExtraInfo['sub_service_id']) && $getExtraInfo['sub_service_id'] == 2) {

            $getChat = json_decode($getAppointment->video_link, true);
            if (isset($getChat['chat_id'])) {
                $chatId = $getChat['chat_id'];
            }
            else {
                $chatId = 'chat_'.$patientId.'_'.$userId.'_'.generateNewCode(9, 2);
                $getAppointment->video_link = json_encode([
                    'chat_id' => $chatId
                ]);
            }

            $dataResult['type'] = 'Chat';
            $dataResult['chat_id'] = $chatId;

        }
        else {

            $getVideo = json_decode($getAppointment->video_link, true);
            $agoraId = $getVideo['id'] ?? '';

            if (strlen($agoraId) <= 0) {
                return [
                    'success' => 95,
                    'message' => 'Appointment not started'
                ];
            }

            $agoraChannel = $getVideo['channel'] ?? '';
            $agoraUidDoctor = $getVideo['uid_doctor'] ?? '';
            $agoraUidPatient = $getVideo['uid_patient'] ?? '';
            $agoraTokenDoctor = $getVideo['token_doctor'] ?? '';
            $agoraTokenPatient = $getVideo['token_patient'] ?? '';

            $getAppointment->online_meeting = 2;
            $getAppointment->time_start_meeting = date('Y-m-d H:i:s');
            $getAppointment->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_doctor' => $agoraUidDoctor,
                'uid_patient' => $agoraUidPatient,
                'token_doctor' => $agoraTokenDoctor,
                'token_patient' => $agoraTokenPatient
            ]);

            $getAppointment->save();

            $dataResult['type'] = 'Video Call';
            $dataResult['video_app_id'] = $agoraId;
            $dataResult['video_channel'] = $agoraChannel;
            $dataResult['video_uid'] = $type == 1 ? $agoraUidPatient : $agoraUidDoctor;
            $dataResult['video_token'] = $type == 1 ? $agoraTokenPatient : $agoraTokenDoctor;

        }

        $getAppointment->online_meeting = 2;
        $getAppointment->time_start_meeting = null;
        $getAppointment->save();

        return [
            'success' => 80,
            'data' => $dataResult
        ];
    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return bool|mixed
     */
    public function meetingCallFinish($appointmentId, $userId)
    {
        $getAppointmentData = $this->checkMeeting($appointmentId, $userId);
        if ($getAppointmentData['success'] != 80) {
            return false;
        }

        $getAppointment = $getAppointmentData['data'];

        $getAppointment->online_meeting = 80;
        $getAppointment->save();

        return $getAppointment;

    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return bool|mixed
     */
    public function meetingCallFailed($appointmentId, $userId)
    {
        $getAppointmentData = $this->checkMeeting($appointmentId, $userId);
        if ($getAppointmentData['success'] != 80) {
            return false;
        }

        $getAppointment = $getAppointmentData['data'];

        $getAppointment->online_meeting = 1;
        $getAppointment->attempted += 1;
        $getAppointment->save();

        return $getAppointment;

    }

    /**
     * @param $appointmentId
     * @param $doctorId
     * @param $saveData
     * @param $saveProducts
     * @param $getDoctorPrescription
     * @return bool|mixed
     */
    public function appointmentDiagnosis($appointmentId, $doctorId, $saveData, $saveProducts, $getDoctorPrescription)
    {
        $getAppointment = AppointmentDoctor::whereIn('status', [3,4])->where('id', '=', $appointmentId)
            ->where('doctor_id', '=', $doctorId)->first();
        if (!$getAppointment) {
            return false;
        }

        $listDoctorPrescription = [];
        if ($getDoctorPrescription) {
            foreach ($getDoctorPrescription as $listImage) {
                $image = base64_to_jpeg($listImage);
                $destinationPath = 'synapsaapps/users/'.$getAppointment->user_id.'/doctor';
                $set_file_name = date('Ymd').'_'.md5('doctor_prescription'.strtotime('now').rand(0, 100)).'.jpg';
                $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
                if ($getFile) {
                    $getImage = env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name;
                    $listDoctorPrescription[] = $getImage;
                }
            }
        }

        DB::beginTransaction();

        $getFormPatient = json_decode($getAppointment->form_patient, true);
        $bodyHeight = $getFormPatient['body_height'] ?? '';
        $bodyWeight = $getFormPatient['body_weight'] ?? '';
        $bloodPressure = $getFormPatient['blood_pressure'] ?? '';
        $bodyTemperature = $getFormPatient['body_temperature'] ?? '';
        $symptoms = $getFormPatient['symptoms'] ?? '';

        $getAppointment->form_patient = json_encode([
            'body_height' => $saveData['body_height'] ?? $bodyHeight,
            'body_weight' => $saveData['body_weight'] ?? $bodyWeight,
            'blood_pressure' => $saveData['blood_pressure'] ?? $bloodPressure,
            'body_temperature' => $saveData['body_temperature'] ?? $bodyTemperature,
            'symptoms' => $saveData['symptoms'] ?? $symptoms
        ]);

        $getAppointment->diagnosis = isset($saveData['diagnosis']) ? strip_tags($saveData['diagnosis']) : '';
        $getAppointment->treatment = isset($saveData['treatment']) ? strip_tags($saveData['treatment']) : '';
        $getAppointment->doctor_prescription = json_encode($listDoctorPrescription) ?? null;
        $getAppointment->status = 80;
        $getAppointment->save();

        foreach ($saveProducts as $item) {
            AppointmentDoctorProduct::create([
                'appointment_doctor_id' => $appointmentId,
                'product_id' => $item['product_id'] ?? '',
                'product_name' => $item['product_name'] ?? '',
                'product_qty' => $item['product_qty'] ?? '',
                'product_qty_checkout' => 0,
                'product_price' => $item['product_price'] ?? '',
                'dose' => $item['dose'] ?? '',
                'type_dose' => $item['type_dose'] ?? '',
                'period' => $item['period'] ?? '',
                'note' => $item['note'] ?? '',
                'choose' => 0,
                'status' => 1
            ]);
        }

        DB::commit();

        return $getAppointment;

    }

}
