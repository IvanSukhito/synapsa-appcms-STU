<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LogEmail extends Model
{
    protected $table = 'log-email';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'email',
        'subject',
        'content',
        'response',
        'status',
        'error_message',
        'browser',
        'ip_address',
        'trigger',
        'created_at',
    ];

}
