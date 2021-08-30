<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

    public function getProduct()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

}
