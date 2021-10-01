<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentLab extends Model
{
    protected $table = 'appointment_lab';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_id',
        'user_id',
        'user_email',
        'type_appointment',
        'patient_name',
        'date',
        'time_start',
        'time_end',
        'form_patient',
        'total_test',
        'extra_info',
        'status'
    ];

    public function getAppointmentLabDetails()
    {
        return $this->hasMany(AppointmentLabDetails::class, 'appointment_lab_id', 'id');
    }

}
