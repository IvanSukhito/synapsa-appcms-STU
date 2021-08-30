<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Sliders extends Model
{
    protected $table = 'sliders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'image',
        'target',
        'orders',
        'status'


    ];




}
