<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentLab extends Model
{
    protected $table = 'appointment_lab';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'klinik_id',
        'code',
        'schedule_id',
        'service_id',
        'user_id',
        'patient_name',
        'patient_email',
        'type_appointment',
        'date',
        'time_start',
        'time_end',
        'form_patient',
        'total_test',
        'extra_info',
        'status'
    ];
    protected $appends = [
        'status_appointment',
    ];

    protected $dates = [
        'created_at',
    ];

    public function getFormPatientAttribute()
    {
        return isset($this->form_patient) && strlen($this->form_patient) > 1 ? env('OSS_URL').'/'.$this->form_patient : '';
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->format('H:i:s Y-m-d ');
    }

    public function getStatusAppointmentAttribute()
    {
        $getList = get_list_appointment();
        return $getList[$this->status] ?? $this->status;
    }

    public function getAppointmentLabDetails()
    {
        return $this->hasMany(AppointmentLabDetails::class, 'appointment_lab_id', 'id');
    }


}
