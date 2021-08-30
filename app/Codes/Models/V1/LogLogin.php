<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    protected $table = 'log-login';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'browser',
        'ip_address'
    ];




}
