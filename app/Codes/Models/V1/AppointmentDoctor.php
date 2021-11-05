<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctor extends Model
{
    protected $table = 'appointment_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'klinik_id',
        'schedule_id',
        'service_id',
        'doctor_id',
        'doctor_name',
        'patient_name',
        'patient_email',
        'user_id',
        'type_appointment',
        'date',
        'time_start',
        'time_end',
        'video_link',
        'form_patient',
        'diagnosis',
        'treatment',
        'doctor_prescription',
        'extra_info',
        'online_meeting',
        'time_start_meeting',
        'attempted',
        'status',
        'message'
    ];

    protected $appends = [
        'status_appointment',
        'online_meeting_nice'
    ];

    public function getAppointmentDoctorProduct()
    {
        return $this->hasMany(AppointmentDoctorProduct::class, 'appointment_doctor_id', 'id');
    }

     public function getStatusAppointmentAttribute()
     {
         $getList = get_list_appointment();
         return $getList[$this->status] ?? $this->status;
     }

     public function getOnlineMeetingNiceAttribute()
     {
         $getList = get_list_online_meeting();
         return $getList[$this->online_meeting] ?? $this->online_meeting;
     }

}
