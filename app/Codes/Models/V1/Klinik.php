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
        'logo',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'status',
    ];

    protected $appends = [
        'logo_full'
    ];

    public function getAdmin()
    {
        return $this->hasMany(Admin::class, 'klinik_id', 'id');
    }

    public function getLogoFullAttribute()
    {
        if (strlen($this->logo) > 0) {
            return env('OSS_URL').'/'.$this->logo;
        }
        return asset('assets/cms/images/no-img.png');
    }
}
