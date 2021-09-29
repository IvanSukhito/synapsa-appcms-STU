<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ConsultationDetails extends Model
{
    protected $table = 'consultation_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'consultation_id',
        'product_id',
        'product_name',
        'product_qty',
        'product_price',
        'doctor_id',
        'doctor_name',
    ];



}
