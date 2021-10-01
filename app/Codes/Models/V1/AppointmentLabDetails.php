<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentLabDetails extends Model
{
    protected $table = 'appointment_lab_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'appointment_lab_id',
        'lab_id',
        'lab_name',
        'lab_price',
        'test_results',
        'status'
    ];

    public function getAppointmentLab()
    {
        return $this->belongsTo(AppointmentLab::class, 'appointment_lab_id', 'id');
    }

}
