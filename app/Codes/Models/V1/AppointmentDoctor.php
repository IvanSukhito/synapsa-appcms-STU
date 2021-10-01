<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctor extends Model
{
    protected $table = 'appointment_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_id',
        'doctor_id',
        'doctor_name',
        'user_id',
        'type_appointment',
        'video_link',
        'form_patient',
        'diagnosis',
        'list_recipe',
        'extra_info',
        'status'
    ];

}
