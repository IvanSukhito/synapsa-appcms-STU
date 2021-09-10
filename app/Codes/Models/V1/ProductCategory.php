<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon',
        'orders',
        'status'
    ];

    protected $appends = [
        'icon_full'
    ];

    public function getIconFullAttribute()
    {
        return asset('assets/cms/images/no-img.png');
//        return strlen($this->icon) > 0 ? asset('uploads/product/'.$this->icon) : asset('assets/cms/images/no-img.png');
    }

    public function getProduct()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

}
