<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;

class DoctorLogic
{
    public function __construct()
    {
    }

    public function doctorInfo($doctorId, $serviceId)
    {
        return Doctor::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor_service.service_id', '=', $serviceId)
            ->where('doctor.id', '=', $doctorId)
            ->where('users.doctor','=', 1)->first();
    }

    public function getSchedule($doctorId, $serviceId, $date)
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

    public function checkSchedule($doctorId, $serviceId, $scheduleId, $date, $raw = 0)
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

        $getAppointmentDoctor = AppointmentDoctor::where('date', '=', $date)->where('time_start', '=', $getSchedule->time_start)->first();
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

    public function setSchedule($doctorId, $serviceId, $scheduleId, $date, $getUser, $getDoctor, $getService, $getTransaction, $newCode)
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

}
