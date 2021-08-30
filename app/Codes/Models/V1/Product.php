<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_category_id',
        'sku',
        'name',
        'image',
        'price',
        'unit',
        'desc',
        'stock',
        'stock_flag'
    ];

    public function getCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'product_tagging', 'product_id', 'tagging_id');
    }

}
