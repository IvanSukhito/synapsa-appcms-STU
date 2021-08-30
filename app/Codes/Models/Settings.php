<?php

namespace App\Codes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Settings extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'key',
        'value',
        'type'
    ];

    public static function boot()
    {
        parent::boot();

        self::updated(function($model){
            Cache::forget('settings');
        });

        self::deleting(function($model){
            Cache::forget('settings');
        });
    }

}
