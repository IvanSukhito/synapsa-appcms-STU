<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon',
        'orders',
        'settings',
        'type',
        'status'
    ];
}
