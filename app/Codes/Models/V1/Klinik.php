<?php

namespace App\Codes\Models\V1;

use App\Codes\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class Klinik extends Model
{
    protected $table = 'klinik';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'address',
        'no_telp',
        'email',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'status',
    ];

    public function getAdmin()
    {
        return $this->hasMany(Admin::class, 'klinik_id', 'id');
    }




}
