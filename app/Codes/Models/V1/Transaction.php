<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transaction';
    protected $primaryKey = 'id';
    protected $fillable = [
        'klinik_id',
        'user_id',
        'code',
        'shipping_id',
        'shipping_name',
        'payment_id',
        'payment_name',
        'payment_detail',
        'receiver_name',
        'receiver_address',
        'receiver_phone',
        'shipping_address_name',
        'shipping_address',
        'shipping_city_id',
        'shipping_city_name',
        'shipping_district_id',
        'shipping_district_name',
        'shipping_subdistrict_id',
        'shipping_subdistrict_name',
        'shipping_zipcode',
        'type',
        'total_qty',
        'subtotal',
        'shipping_price',
        'total',
        'extra_info',
        'status'
    ];

    protected $appends = [
        'type_transaction',
        'status_transaction',
        'total_nice',
        'subtotal_nice',
        'shipping_price_nice'
    ];


    public function getShippingPriceNiceAttribute()
    {
        return intval($this->shipping_price) > 0 ? number_format($this->shipping_price, 0, '.', '.') : 0;
    }

    public function getTotalNiceAttribute()
    {
        return intval($this->total) > 0 ? number_format($this->total, 0, '.', '.') : 0;
    }

    public function getSubtotalNiceAttribute()
    {
        return intval($this->subtotal) > 0 ? number_format($this->subtotal, 0, '.', '.') : 0;
    }

    public function getTypeTransactionAttribute()
    {
        $getList = get_list_type_transaction();
        return $getList[$this->type] ?? $this->type;
    }

    public function getStatusTransactionAttribute()
    {
        $getList = get_list_transaction();
        return $getList[$this->status] ?? $this->status;
    }

    public function getTransactionDetails()
    {
        return $this->hasMany(TransactionDetails::class, 'transaction_id', 'id');
    }

}
