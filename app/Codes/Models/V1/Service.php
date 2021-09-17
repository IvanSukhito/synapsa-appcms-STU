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
      'status'
    ];


    public function getDoctor()
    {
        return $this->belongsToMany(Doctor::class, 'doctor-service', 'service_id', 'doctor_id');
    }
    public function getLab()
    {
        return $this->belongsToMany(Lab::class, 'lab-service', 'lab_id', 'service_id');
    }


}
