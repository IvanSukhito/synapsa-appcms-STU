<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LogServiceTransaction extends Model
{
    protected $table = 'log_service_transaction';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'transaction_refer_id',
        'service',
        'type_payment',
        'type_transaction',
        'results'
    ];

}
