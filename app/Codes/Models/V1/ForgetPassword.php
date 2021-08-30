<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ForgetPassword extends Model
{
    protected $table = 'forget_password';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'email',
        'code',
        'status',
        'attempt'
    ];




}
