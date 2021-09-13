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
        'stock_flag',
        'status'
    ];

    protected $appends = [
        'desc_details',
        'image_full',
        'price_nice',
    ];
    public function getPriceNiceAttribute()
    {
        return intval($this->price) > 0 ? number_format($this->price, 0, '.', '.') : 0;
    }

    public function getDescDetailsAttribute()
    {
        return json_decode($this->desc, TRUE);
    }

    public function getImageFullAttribute()
    {
        //return asset('assets/cms/images/no-img.png');
        return strlen($this->image) > 0 ? asset($this->image) : asset('assets/cms/images/no-img.png');
    }


    public function getCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'product_tagging', 'product_id', 'tagging_id');
    }


}
