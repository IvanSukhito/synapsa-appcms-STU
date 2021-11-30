<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'product_category_id',
        'klinik_id',
        'sku',
        'name',
        'image',
        'price',
        'unit',
        'desc',
        'stock',
        'stock_flag',
        'type',
        'status',
        'top'
    ];

    protected $appends = [
        'desc_details',
        'image_full',
        'price_nice',
        'stock_flag_nice',
        'status_nice',
    ];

    public function getPriceNiceAttribute()
    {
        return intval($this->price) > 0 ? number_format($this->price, 0, ',', '.') : 0;
    }

    public function getStockFlagNiceAttribute()
    {
        $getList = get_list_stock_flag();
        return $getList[$this->stock_flag] ?? '';
    }

    public function getStatusNiceAttribute()
    {
        $getList = get_list_available_non_available();
        return $getList[$this->status] ?? '';
    }


    public function getDescDetailsAttribute()
    {
        return json_decode($this->desc, TRUE);
    }

    public function getImageFullAttribute()
    {
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
        //return strlen($this->image) > 0 ? asset($this->image) : asset('assets/cms/images/no-img.png');
    }


    public function getCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'product_tagging', 'product_id', 'tagging_id');
    }

    public static function boot()
    {
        parent::boot();

        self::updating(function($model){
            if($model->stock <= 0 && $model->stock_flag == 2) {
                $model->status = 99;
            }
        });

    }

}
