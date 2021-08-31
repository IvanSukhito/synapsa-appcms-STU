<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ForgetPassword extends Model
{
    protected $table = 'forget_password';
    protected $primaryKey = 'id';
    protected $fillable = [
        'users_id',
        'email',
        'code',
        'status',
        'attempt'
    ];




}
