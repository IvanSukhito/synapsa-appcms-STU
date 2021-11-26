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
        'product_qty_checkout',
        'product_price',
        'dose',
        'type_dose',
        'period',
        'note',
        'choose',
        'status'
    ];
    protected $appends = [
        'product_price_nice',
    ];

    public function getProductPriceNiceAttribute()
    {
        return intval($this->product_price) > 0 ? number_format($this->product_price, 0, ',', '.') : 0;
    }

    public function getAppointmentDoctor()
    {
        return $this->hasMany(AppointmentDoctor::class, 'appointment_doctor_id', 'id');
    }

}
