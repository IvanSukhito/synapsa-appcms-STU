<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $table = 'consultation';
    protected $primaryKey = 'id';
    protected $fillable = [
        'klinik_id',
        'doctor_id',
        'user_id',
        'diagnosis',
        'medication',
        'upload_rescipe',
    ];



}
