<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\Service;
use Illuminate\Support\Facades\DB;

class DoctorLogic
{
    public function __construct()
    {
    }

    /**
     * @param $doctorId
     * @param null $serviceId
     * @return mixed
     */
    public function doctorInfo($doctorId, $serviceId = null)
    {
        if ($serviceId) {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
                ->where('doctor_service.service_id', '=', $serviceId)
                ->where('doctor.id', '=', $doctorId)
                ->where('users.doctor','=', 1);
        }
        else {
            $getData = Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, 0 AS price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
                ->where('doctor.id', '=', $doctorId)
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
    public function scheduleGet($doctorId, $serviceId, $date): array
    {
        $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
            ->where('date_available', '=', $date)
            ->get();
        if (!$getDoctorSchedule) {
            $getWeekday = intval(date('w', strtotime($date)));
            $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
                ->where('weekday', $getWeekday)->get();
        }

        $getList = get_list_book();
        $getAppointmentDoctor = AppointmentDoctor::where('date', '=', $date)->get();
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
     * @param $doctorId
     * @param $serviceId
     * @param $scheduleId
     * @param $date
     * @param int $raw
     * @return array|int|int[]
     */
    public function scheduleCheck($doctorId, $serviceId, $scheduleId, $date, int $raw = 0)
    {
        $getSchedule = DoctorSchedule::where('doctor_id', '=', $doctorId)->where('service_id', '=', $serviceId)
            ->where('id', '=', $scheduleId)->first();
        if (!$getSchedule) {
            if ($raw == 1) {
                return [
                    'success' => 0
                ];
            }
            return 0;
        }

        $getAppointmentDoctor = AppointmentDoctor::where('doctor_id', '=', $doctorId)->where('date', '=', $date)
            ->where('time_start', '=', $getSchedule->time_start)->first();
        if ($getAppointmentDoctor) {
            if ($raw == 1) {
                return [
                    'success' => 0
                ];
            }
            return 0;
        }

        if ($raw == 1) {
            return [
                'success' => 1,
                'schedule' => $getSchedule
            ];
        }
        else {
            return 1;
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
            ->where('time_start', '=', $timeStart)->first();
        if ($getAppointmentDoctor) {
            return 0;
        }
        return 1;
    }

    /**
     * @param $doctorId
     * @param $serviceId
     * @param $scheduleId
     * @param $date
     * @param $getUser
     * @param $getDoctor
     * @param $getService
     * @param $getTransaction
     * @param $newCode
     * @return int
     */
    public function appointmentCreate($doctorId, $serviceId, $scheduleId, $date, $getUser, $getDoctor, $getService, $getTransaction, $newCode): int
    {
        $getData = $this->checkSchedule($doctorId, $serviceId, $scheduleId, $date, 1);
        if ($getData['success'] == 1) {
            $getSchedule = $getData['schedule'];

            AppointmentDoctor::create([
                'transaction_id' => $getTransaction->id,
                'klinik_id' => $getTransaction->klinik_id,
                'code' => $newCode,
                'schedule_id' => $getSchedule->id,
                'service_id' => $getSchedule->service_id,
                'doctor_id' => $getSchedule->doctor_id,
                'user_id' => $getTransaction->user_id,
                'type_appointment' => $getService ? $getService->name : '',
                'patient_name' => $getUser ? $getUser->fullname : '',
                'patient_email' => $getUser ? $getUser->email : '',
                'doctor_name' => $getDoctor ? $getDoctor->fullname : '',
                'date' => $getSchedule->date_available,
                'time_start' => $getSchedule->time_start,
                'time_end' => $getSchedule->time_end,
                'status' => 1
            ]);

            return 1;

        }

        return 0;

    }

    /**
     * Flag
     * 1. Patient
     * 2. Doctor
     * @param $appointmentId
     * @param $flag
     * @param null $userId
     * @param null $doctorId
     * @return false
     */
    public function appointmentInfo($appointmentId, $flag, $userId = null, $doctorId = null): bool
    {
        if ($flag == 1) {
            $getAppointment = AppointmentDoctor::whereIn('status')->where('id', $appointmentId)
                ->where('user_id', $userId)->first();
        }
        else {
            $getAppointment = AppointmentDoctor::whereIn('status', [1,2])->where('id', $appointmentId)
                ->where('doctor_id', $doctorId)->first();
        }
        if ($getAppointment) {
            return $getAppointment;
        }
        return false;
    }

    /**
     * Flag
     * 1. Patient
     * 2. Doctor
     * @param $appointmentId
     * @param $flag
     * @param null $userId
     * @param null $doctorId
     * @return int
     */
    public function appointmentApprove($appointmentId, $flag, $userId = null, $doctorId = null): int
    {
        if ($flag == 1) {
            $getAppointment = AppointmentDoctor::whereIn('status', [1,2])->where('id', $appointmentId)
                ->where('user_id', $userId)->first();
        }
        else {
            $getAppointment = AppointmentDoctor::whereIn('status', [1,2])->where('id', $appointmentId)
                ->where('doctor_id', $doctorId)->first();
        }
        if ($getAppointment) {
            $getService = Service::where('id', $getAppointment->service_id)->first();
            if ($getService && $getService->type == 1) {
                $getAppointment->video_link = '';
                $getAppointment->online_meeting = 1;
                $getAppointment->status = 3;
            }
            else {
                $getAppointment->status = 4;
            }
            $getAppointment->save();
            return 1;
        }
        return 0;
    }

    /**
     * Flag
     * 1. Patient
     * 2. Doctor
     * @param $appointmentId
     * @param $flag
     * @param null $userId
     * @param null $doctorId
     * @return int
     */
    public function appointmentCancel($appointmentId, $flag, $userId = null, $doctorId = null): int
    {
        if ($flag == 1) {
            $getAppointment = AppointmentDoctor::whereIn('status', [1,2,80])->where('id', $appointmentId)
                ->where('user_id', $userId)->first();
        }
        else {
            $getAppointment = AppointmentDoctor::whereIn('status', [1,2,80])->where('id', $appointmentId)
                ->where('doctor_id', $doctorId)->first();
        }
        if ($getAppointment) {
            $getAppointment->video_link = '';
            $getAppointment->online_meeting = 0;
            $getAppointment->status = 99;
            $getAppointment->save();
            return 1;
        }
        return 0;
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
        $getAppointment = AppointmentDoctor::where('id', $appointmentId)->where('user_id', $getUser->id)
            ->where('doctor_id', $getDoctor->id)->first();
        if ($getAppointment) {
            if ($this->checkAvailableSchedule($doctorId, $date, $timeStart)) {
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
     * @param $saveData
     * @return int
     */
    public function appointmentFillForm($appointmentId, $userId, $saveData): int
    {
        $getAppointment = AppointmentDoctor::where('id', $appointmentId)->where('user_id', $userId)->first();
        if (!$getAppointment) {
            return 0;
        }

        $saveFormPatient = [
            'body_height' => strip_tags($saveData['body_height']) ?? '',
            'body_weight' => strip_tags($saveData['body_weight']) ?? '',
            'blood_pressure' => strip_tags($saveData['blood_pressure']) ?? '',
            'body_temperature' => strip_tags($saveData['body_temperature']) ?? '',
            'medical_checkup' => $saveData['medical_checkup'] ?? [],
            'complaint' => strip_tags($saveData['complaint']) ?? ''
        ];

        $getAppointment->form_patient = json_encode($saveFormPatient);
        $getAppointment->save();

        return 1;

    }

    /**
     * @param $appointmentId
     * @param $doctorId
     * @return array
     */
    public function meetingCreate($appointmentId, $doctorId): array
    {
        $getAppointment = AppointmentDoctor::where('status', '=', 3)->where('id', $appointmentId)
            ->where('doctor_id', $doctorId)->first();
        if (!$getAppointment) {
            return [];
        }

        $getAppointment->online_meeting = 2;
        $getAppointment->time_start_meeting = null;

        $agoraLogic = new agoraLogic();
        if (strlen($getAppointment->video_link) <= 10) {
            $agoraChannel = $getAppointment->id.$getAppointment->user_id.'tele'.md5($getAppointment->date.$getAppointment->time_start .$getAppointment->time_end.$getAppointment->doctor_id.$getAppointment->user_id.rand(0,100));
            $agoraUidDoctor = rand(1000000000, 9999999999);
            $agoraUidPatient = rand(1000000000, 9999999999);
            $agoraTokenDoctor = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDoctor);
            $agoraTokenPatient = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPatient);
            $agoraId = $agoraLogic->getAppId();

            $getAppointment->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_doctor' => $agoraUidDoctor,
                'uid_patient' => $agoraUidPatient,
                'token_doctor' => $agoraTokenDoctor,
                'token_patient' => $agoraTokenPatient
            ]);

        }
        else {
            $getVideo = json_decode($getAppointment->video_link, true);
            $agoraId = $getVideo['id'] ?? '';
            $agoraChannel = $getVideo['channel'] ?? '';
            $agoraUidDoctor = $getVideo['uid_doctor'] ?? '';
            $agoraUidPatient = $getVideo['uid_patient'] ?? '';
            $agoraTokenDoctor = $agoraLogic->createRtcToken($agoraChannel, $agoraUidDoctor);
            $agoraTokenPatient = $agoraLogic->createRtcToken($agoraChannel, $agoraUidPatient);

            $getAppointment->video_link = json_encode([
                'id' => $agoraId,
                'channel' => $agoraChannel,
                'uid_doctor' => $agoraUidDoctor,
                'uid_patient' => $agoraUidPatient,
                'token_doctor' => $agoraTokenDoctor,
                'token_patient' => $agoraTokenPatient
            ]);

        }

        $getAppointment->save();

        return [
            'appointment' => $getAppointment,
            'video_id' => $agoraId,
            'video_channel' => $agoraChannel,
            'video_uid_doctor' => $agoraUidDoctor,
            'video_uid_patient' => $agoraUidPatient,
            'video_token_doctor' => $agoraTokenDoctor,
            'video_token_patient' => $agoraTokenPatient
        ];

    }

    /**
     * @param $appointmentId
     * @param $userId
     * @return array
     */
    public function meetingGetInfo($appointmentId, $userId): array
    {
        $getAppointment = AppointmentDoctor::where('status', '=', 3)->where('id', $appointmentId)
            ->where('user_id', $userId)->first();
        if (!$getAppointment) {
            return [];
        }

        if (strlen($getAppointment->video_link) <= 10) {
            return [];
        }
        else {

            $getVideo = json_decode($getAppointment->video_link, true);
            $agoraId = $getVideo['id'] ?? '';
            $agoraChannel = $getVideo['channel'] ?? '';
            $agoraUidDoctor = $getVideo['uid_doctor'] ?? '';
            $agoraUidPatient = $getVideo['uid_patient'] ?? '';
            $agoraTokenDoctor = $getVideo['token_doctor'] ?? '';
            $agoraTokenPatient = $getVideo['token_patient'] ?? '';

            return [
                'appointment' => $getAppointment,
                'video_id' => $agoraId,
                'video_channel' => $agoraChannel,
                'video_uid_doctor' => $agoraUidDoctor,
                'video_uid_patient' => $agoraUidPatient,
                'video_token_doctor' => $agoraTokenDoctor,
                'video_token_patient' => $agoraTokenPatient
            ];

        }
    }

    /**
     * @param $appointmentId
     * @param $doctorId
     * @return bool
     */
    public function meetingCallFinish($appointmentId, $doctorId): bool
    {
        $getAppointment = AppointmentDoctor::where('status', '=', 3)->where('id', $appointmentId)
            ->where('doctor_id', $doctorId)->first();
        if (!$getAppointment) {
            return false;
        }

        $getAppointment->online_meeting = 80;
        $getAppointment->save();

        return $getAppointment;

    }

    /**
     * @param $appointmentId
     * @param $doctorId
     * @return bool
     */
    public function meetingCallFailed($appointmentId, $doctorId): bool
    {
        $getAppointment = AppointmentDoctor::where('status', '=', 3)->where('id', $appointmentId)
            ->where('doctor_id', $doctorId)->first();
        if (!$getAppointment) {
            return false;
        }

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
     * @return bool
     */
    public function appointmentDiagnosis($appointmentId, $doctorId, $saveData, $saveProducts): bool
    {
        $getAppointment = AppointmentDoctor::whereIn('status', [3,4])->where('id', $appointmentId)
            ->where('doctor_id', $doctorId)->first();
        if (!$getAppointment) {
            return false;
        }

        DB::beginTransaction();

        $getAppointment->diagnosis = strip_tags($saveData['diagnosis']) ?? '';
        $getAppointment->treatment = strip_tags($saveData['treatment']) ?? '';
        $getAppointment->doctor_prescription = json_encode($saveData['doctor_prescription']) ?? null;
        $getAppointment->status = 80;
        $getAppointment->save();

        foreach ($saveProducts as $item) {
            AppointmentDoctorProduct::create([
                'appointment_doctor_id' => $appointmentId,
                'product_id' => $item['product_id'] ?? '',
                'product_name' => $item['product_name'] ?? '',
                'product_qty' => $item['product_qty'] ?? '',
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
