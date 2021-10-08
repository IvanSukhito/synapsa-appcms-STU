<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table = 'shipping';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon',
        'orders',
        'settings',
        'status'
    ];

    protected $appends = [
        'shipping_price',
        'shipping_price_nice'
    ];

    public function getShippingPriceAttribute()
    {
        return 15000;
    }

    public function getShippingPriceNiceAttribute()
    {
        return number_format(15000, 0, ',', '.');
    }

}
