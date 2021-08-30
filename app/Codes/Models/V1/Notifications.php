<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'content',
        'target',
        'is_read',
        'type',
        'date'
    ];




}
