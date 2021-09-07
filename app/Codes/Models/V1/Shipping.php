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
}
