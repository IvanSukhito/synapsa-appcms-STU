<?php

namespace App\Codes\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'username',
        'password',
        'role_id',
        'status'
    ];

    protected $dates = [
        'created_at',
    ];

    protected $hidden = ['password'];

    public function getRole()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->format('H:i:s Y-m-d ');
    }


}
