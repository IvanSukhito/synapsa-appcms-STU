<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class CustomerSupport extends Model
{
    protected $table = 'customer_support';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'type',
        'contact',
        'status'
    ];


}
