<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TempAdDetail extends Model
{
    protected $table = 'temp_ad_detail';
    protected $primaryKey = 'id';
    protected $fillable = [
        'temp_ad_id',
        'appointment_doctor_id',
        'doctor_id',
        'qty',
        'choose'
    ];

}
