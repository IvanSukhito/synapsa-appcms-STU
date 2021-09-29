<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_id',
        'doctor_id',
        'user_id',
        'type_appointment',
        'status'
    ];



}
