<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LabProduct extends Model
{
    protected $table = 'lab_product';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'title',
        'desc',
        'image',
        'benefit',
        'preparation',
    ];

    public function getProduct(){
        return $this->hasMany(Product::class,'product_id','parent_id',);
    }
}
