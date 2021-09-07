<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UsersCartDetail extends Model
{
    protected $table = 'users_cart_detail';
    protected $primaryKey = 'id';
    protected $fillable = [
        'users_cart_id',
        'product_id',
        'qty',
        'choose'
    ];

}
