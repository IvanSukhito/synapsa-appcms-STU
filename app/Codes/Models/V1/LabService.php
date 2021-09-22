<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LabService extends Model
{
    protected $table = 'lab_service';
    protected $keyType = 'char';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'lab_id',
        'service_id',
        'type',
        'price'
    ];
 

}
