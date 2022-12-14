<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LogPhone extends Model
{
    protected $table = 'log-phone';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'phone',
        'content',
        'code_verification',
        'browser',
        'ip_address',
        'trigger'
    ];




}
