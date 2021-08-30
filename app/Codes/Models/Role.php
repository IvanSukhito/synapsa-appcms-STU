<?php

namespace App\Codes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'permission_data',
        'permission_route'
    ];

    public function getAdmin()
    {
        $this->hasMany(Admin::class, 'role_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        self::updated(function($model){
            Cache::forget('role'.$model->id);
        });

        self::deleting(function($model){
            Cache::forget('role'.$model->id);
        });
    }

}
