<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentNurse;
use Illuminate\Support\Facades\DB;

class UserAppointmentLogic
{
    public function __construct()
    {
    }

    /**
     * @param $userId
     * @param $type
     * @param int $limit
     * @return mixed
     */
    public function appointmentList($userId, $type, int $limit = 10)
    {
        $dateNow = date('Y-m-d');

        switch ($type) {
            case 2 : $status = [1];
                break;

            case 3 : $status = [80];
                break;

            case 4 : $status = [2];
                break;

            default : $status = [3,4];
                break;
        }

        $getData = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.user_id', $userId)
            ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
            ->whereIn('appointment_doctor.status', $status);

        if ($type == 1) {
            $getData = $getData->where('appointment_doctor.date', '>=', $dateNow);
        }

        switch ($type) {
            case 2 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->where('appointment_doctor.user_id', $userId)
                ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
                ->whereIn('appointment_doctor.status', [1])
                ->union(
                    AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 
                            0 AS form_patient, 0 AS online_meeting,
                            \'\' AS doctor_category, lab.image AS image, CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, \'\' as extra_info')
                        ->join('appointment_lab_details', function($join){
                            $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                        })
                        ->join('lab', function($join){
                            $join->on('lab.id','=','appointment_lab_details.lab_id')
                                ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                        })
                        ->where('appointment_lab.user_id', $userId)
                        ->whereIn('appointment_lab.status', [1])
                )
                ->union(
                    AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 
                            \'\' as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment, appointment_nurse.date, 
                            \'\' as time_start, \'\' as time_end, appointment_nurse.status, shift_qty as shift_qty, 
                            0 AS form_patient, 0 AS online_meeting,
                            \'\' AS doctor_category, \'\' AS image, \'\' AS image_full, \'\' as extra_info')
                        ->where('appointment_nurse.user_id', $userId)
                        ->whereIn('appointment_nurse.status', [1])
                );
                break;
            case 3 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
                ->where('appointment_doctor.user_id', $userId)
                ->where('appointment_doctor.status', '=', 80)
                ->union(
                    AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, 0 as extra_info')
                        ->join('appointment_lab_details', function($join){
                            $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                        })
                        ->join('lab', function($join){
                            $join->on('lab.id','=','appointment_lab_details.lab_id')
                                ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                        })
                        ->where('appointment_lab.user_id', $userId)
                        ->where('appointment_lab.status', '=', 80)
                )->union(
                    AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 
                            \'\' as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment, appointment_nurse.date, 
                            \'\' as time_start, \'\' as time_end, appointment_nurse.status, shift_qty as shift_qty, 
                            0 AS form_patient, 0 AS online_meeting,
                            \'\' AS doctor_category, \'\' AS image, \'\' AS image_full, \'\' as extra_info')
                        ->where('appointment_nurse.user_id', $userId)
                        ->where('appointment_nurse.status', '=',80)
                );
                break;
            case 4 : $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                ->join('users', 'users.id', '=', 'doctor.user_id')
                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
                ->where('appointment_doctor.user_id', $userId)
                ->whereIn('appointment_doctor.status', [2,90])
                ->union(
                    AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, 0 as extra_info')
                        ->join('appointment_lab_details', function($join){
                            $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                        })
                        ->join('lab', function($join){
                            $join->on('lab.id','=','appointment_lab_details.lab_id')
                                ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                        })
                        ->where('appointment_lab.user_id', $userId)
                        ->whereIn('appointment_lab.status', [2,90])
                )->union(
                    AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, 0 as time_start, 0 as time_end, appointment_nurse.status, shift_qty as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full, 0 as extra_info')
                        ->where('appointment_nurse.user_id', $userId)
                        ->whereIn('appointment_nurse.status', [2,90])
                );
                break;
            default:
                $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
                    appointment_doctor.doctor_name AS janji_name, 1 AS type, \'doctor\' AS type_name,appointment_doctor.type_appointment, appointment_doctor.date,
                    appointment_doctor.time_start as time_start, appointment_doctor.time_end as time_end, appointment_doctor.status, 0 as shift_qty,
                    IF(LENGTH(appointment_doctor.form_patient) > 10, 1, 0) AS form_patient, online_meeting,
                    doctor_category.name AS doctor_category, users.image AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full, transaction_details.extra_info as extra_info')
                    ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
                    ->join('users', 'users.id', '=', 'doctor.user_id')
                    ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
                    ->join('transaction_details','transaction_details.transaction_id','=','appointment_doctor.transaction_id', 'LEFT')
                    ->where('appointment_doctor.user_id', $userId)
                    ->where('appointment_doctor.date', '>=', $dateNow)
                    ->whereIn('appointment_doctor.status', [3,4])
                    ->union(
                        AppointmentLab::selectRaw('appointment_lab.id, lab.id AS janji_id,
                            lab.name AS janji_name, 2 AS type, \'lab\' AS type_name, appointment_lab.type_appointment, appointment_lab.date,
                            appointment_lab.time_start as time_start, appointment_lab.time_end as time_end, appointment_lab.status, 0 as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, lab.image AS image,
                            CONCAT("'.env('OSS_URL').'/'.'", lab.image) AS image_full, 0 as extra_info')
                            ->join('appointment_lab_details', function($join){
                                $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                                    ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                            })
                            ->join('lab', function($join){
                                $join->on('lab.id','=','appointment_lab_details.lab_id')
                                    ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                            })
                            ->where('appointment_lab.user_id', $userId)
                            ->where('appointment_lab.date', '>=', $dateNow)
                            ->whereIn('appointment_lab.status', [3,4])
                    )
                    ->union(
                        AppointmentNurse::selectRaw('appointment_nurse.id, appointment_nurse.schedule_id as janji_id, 0 as janji_name, 3 AS type, \'nurse\' AS type_name, appointment_nurse.type_appointment,
                             appointment_nurse.date, 0 as time_start, 0 as time_end, appointment_nurse.status, shift_qty as shift_qty, 0 AS form_patient, 0 AS online_meeting,
                            0 AS doctor_category, 0 AS image, CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full, 0 as extra_info')
                            ->where('appointment_nurse.user_id', $userId)
                            ->where('appointment_nurse.date', '>=', $dateNow)
                            ->whereIn('appointment_nurse.status', [3,4])
                    );
                break;
        }

        return $data->orderBy('id','DESC')->orderBy('time_start','DESC')->paginate($limit);

    }

    public function appointmentInfo($appointmentId, $type)
    {

    }

}
