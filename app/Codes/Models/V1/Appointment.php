<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'user_id',
        'status',
    ];



}
