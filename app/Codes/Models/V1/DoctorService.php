<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorService extends Model
{
    protected $table = 'doctor_service';
    protected $keyType = 'char';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'doctor_id',
        'service_id',
        'type',
        'price'
    ];
 

}
