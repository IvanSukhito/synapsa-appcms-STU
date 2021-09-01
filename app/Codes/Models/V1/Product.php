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

    protected $appends = [
        'upload_product_image'
    ];


    public function getCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'product_tagging', 'product_id', 'tagging_id');
    }

    public function getUploadProductImageAttribute()
    {
        return strlen($this->image) > 0 ? asset('uploads/users/'.$this->image) : asset('assets/cms/images/no-img.png');
    }


}