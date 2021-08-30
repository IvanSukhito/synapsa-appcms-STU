<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Klinik extends Model
{
    protected $table = 'klinik';
    protected $primaryKey = 'id';
    protected $fillable = [
      'name',
      'status'
    ];



}
