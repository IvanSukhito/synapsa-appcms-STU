<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service';
    protected $primaryKey = 'id';
    protected $fillable = [
      'name',
      'status'
    ];

    
    public function getService()
    {
        return $this->belongsToMany(Doctor::class, 'doctor-service', 'doctor_id', 'service_id');
    }


}
