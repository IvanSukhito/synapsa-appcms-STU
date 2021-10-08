<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctor extends Model
{
    protected $table = 'appointment_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
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
        'status'
    ];

    protected $appends = [
        'status_appointment',
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

}
