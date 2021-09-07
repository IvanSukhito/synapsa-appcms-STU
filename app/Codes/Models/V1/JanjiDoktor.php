<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class JanjiDoktor extends Model
{
    protected $table = 'janji_temu_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [

      'doctor_id',
      'book_day',
      'book_at'

    ];

}
