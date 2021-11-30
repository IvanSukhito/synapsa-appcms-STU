<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class MedicineType extends Model
{
    protected $table = 'medicine_type';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'status'
    ];




}
