<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentNurse extends Model
{
    protected $table = 'appointment_nurse';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'klinik_id',
        'schedule_id',
        'service_id',
        'user_id',
        'patient_name',
        'patient_email',
        'type_appointment',
        'date',
        'shift_qty',
        'extra_info',
        'status'
    ];


    protected $dates = [
        'created_at',
    ];

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->format('H:i:s Y-m-d ');
    }



}
