<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ProductTagging extends Model
{
    protected $table = 'product_tagging';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_id',
        'tagging_id'
    ];



}
