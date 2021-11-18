<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_category_id',
        'klinik_id',
        'product_category_name',
        'klinik_name',
        'klinik_address',
        'klinik_no_telp',
        'klinik_email',
        'product_name',
        'product_image',
        'price_product_klinik',
        'price_product_synapsa',
        'product_unit',
        'product_desc',
        'product_type',
        'status',
    ];

    protected $appends = [
        'product_image_full',
        'price_nice_product_klinik',
        'price_nice_product_synapsa',
    ];

    public function getPriceNiceProductKlinikAttribute()
    {
        return intval($this->price_product_klinik) > 0 ? number_format($this->price_product_klinik, 0, ',', '.') : 0;
    }

    public function getPriceNiceProductSynapsaAttribute()
    {
        return intval($this->price_product_synapsa) > 0 ? number_format($this->price_product_synapsa, 0, ',', '.') : 0;
    }

    public function getProductImageFullAttribute()
    {

        if (strlen($this->product_image) > 0) {
            return env('OSS_URL').'/'.$this->product_image;
        }
        return asset('assets/cms/images/no-img.png');
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
