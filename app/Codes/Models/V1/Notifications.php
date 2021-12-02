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
        'type',
        'target_menu',
        'target_id',
        'is_read',
        'date'
    ];

}
