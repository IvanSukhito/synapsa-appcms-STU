<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UsersCart extends Model
{
    protected $table = 'users_cart';
    protected $primaryKey = 'id';
    protected $fillable = [
      'users_cart_id',
      'product_id',
      'qty',

    ];




}
