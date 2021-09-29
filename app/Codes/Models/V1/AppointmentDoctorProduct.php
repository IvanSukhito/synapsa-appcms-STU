<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctorProduct extends Model
{
    protected $table = 'appointment_doctor_product';
    protected $primaryKey = 'id';
    protected $fillable = [
        'appointment_doctor_id',
        'product_id',
        'product_name',
        'product_qty',
        'product_price',
        'choose',
        'status'
    ];
  

}
