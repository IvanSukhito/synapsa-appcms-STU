<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Faqs extends Model
{
    protected $table = 'faqs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'question'
        'answer',
        'orders',
        'created_by',
        'updated_by'









    ];




}
