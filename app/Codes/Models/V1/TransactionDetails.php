<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $table = 'transaction_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'product_qty',
        'product_price',
        'schedule_id',
        'doctor_id',
        'doctor_name',
        'doctor_price',
        'lab_id',
        'lab_name',
        'lab_price',
        'nurse_id',
        'nurse_shift',
        'nurse_booked',
    ];

    protected $appends = [
        'product_price_nice',
        'doctor_price_nice',
        'lab_price_nice',
    ];

    public function getLabPriceNiceAttribute()
    {
        return intval($this->lab_price) > 0 ? number_format($this->lab_price, 0, ',', '.') : 0;
    }

    public function getDoctorPriceNiceAttribute()
    {
        return intval($this->doctor_price) > 0 ? number_format($this->doctor_price, 0, ',', '.') : 0;
    }

    public function getProductPriceNiceAttribute()
    {
        return intval($this->product_price) > 0 ? number_format($this->product_price, 0, ',', '.') : 0;
    }

    public function getTransaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

}
