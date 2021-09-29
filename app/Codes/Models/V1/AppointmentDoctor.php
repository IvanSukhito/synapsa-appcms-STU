<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctor extends Model
{
    protected $table = 'appointment_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
        'appointment_id',
        'video_link',
        'form_patient',
        'diagnosis',
        'list_product',
        'list_rescipe',
        'extra_info',
        'status',

    ];
    protected $appends = [
        'document_full',
    ];

    public function getDocumentFullAttribute()
    {

        if (strlen($this->document) > 0) {
            return env('OSS_URL').'/'.$this->document;
        }
        return asset('assets/cms/images/no-img.png');
        //return strlen($this->image) > 0 ? asset($this->image) : asset('assets/cms/images/no-img.png');
    }

}
