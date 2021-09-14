<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LabCart extends Model
{
    protected $table = 'lab_cart';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'lab_id',
        'service_id',
        'choose'
    ];

}
