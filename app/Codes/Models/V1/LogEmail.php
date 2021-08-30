<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LogEmail extends Model
{
    protected $table = 'log-email';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'code_verification',
        'browser',
        'ip_address',
        'trigger'

    ];




}
