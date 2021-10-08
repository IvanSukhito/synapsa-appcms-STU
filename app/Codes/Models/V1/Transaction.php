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
        'payment_id',
        'shipping_id',
        'payment_refer_id',
        'payment_service',
        'type_payment',
        'code',
        'payment_name',
        'payment_detail',
        'shipping_name',
        'shipping_address_name',
        'shipping_address',
        'shipping_city_id',
        'shipping_city_name',
        'shipping_district_id',
        'shipping_district_name',
        'shipping_subdistrict_id',
        'shipping_subdistrict_name',
        'shipping_zipcode',
        'shipping_price',
        'total_qty',
        'subtotal',
        'total',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'type',
        'extra_info',
        'payment_info',
        'status'
    ];

    protected $dates = [
        'created_at',
    ];
    protected $appends = [
        'type_transaction',
        'type_transaction2',
        'type_transaction3',
        'status_transaction',
        'total_nice',
        'subtotal_nice',
        'shipping_price_nice'
    ];

    public function getShippingPriceNiceAttribute()
    {
        return intval($this->shipping_price) > 0 ? number_format($this->shipping_price, 0, ',', '.') : 0;
    }

    public function getTotalNiceAttribute()
    {
        return intval($this->total) > 0 ? number_format($this->total, 0, ',', '.') : 0;
    }

    public function getSubtotalNiceAttribute()
    {
        return intval($this->subtotal) > 0 ? number_format($this->subtotal, 0, ',', '.') : 0;
    }

    public function getTypeTransactionAttribute()
    {
        $getList = get_list_type_transaction();
        return $getList[$this->type] ?? $this->type;
    }

    public function getTypeTransaction2Attribute()
    {
        $getList = get_list_type_transaction2();
        return $getList[$this->type] ?? 0;
    }

    public function getTypeTransaction3Attribute()
    {
        $getList = get_list_type_transaction3();
        return $getList[$this->type] ?? 0;
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

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->format('H:i:s Y-m-d ');
    }
}
