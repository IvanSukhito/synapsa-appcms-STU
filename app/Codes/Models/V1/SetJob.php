<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class SetJob extends Model
{
    protected $table = 'set_jobs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'status',
        'params',
        'response'
    ];

}
