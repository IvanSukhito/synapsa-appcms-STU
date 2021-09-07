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
        'status_transaction'
    ];

    public function getTypeTransactionAttribute()
    {
        $getList = get_list_type_transaction();
        return $getList[$this->status] ?? $this->status;
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
