<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service';
    protected $primaryKey = 'id';
    protected $fillable = [
      'name',
      'orders',
      'type',
      'status'
    ];

    protected $appends = [
        'type_nice'
    ];

    public function getTypeNiceAttribute()
    {
        $getList = get_list_type_service();
        return $getList[$this->type] ?? '-';
    }

    public function getDoctor()
    {
        return $this->belongsToMany(Doctor::class, 'doctor-service', 'service_id', 'doctor_id');
    }
    public function getLab()
    {
        return $this->belongsToMany(Lab::class, 'lab-service', 'lab_id', 'service_id');
    }


}
