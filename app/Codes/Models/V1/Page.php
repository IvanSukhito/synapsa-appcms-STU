<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Page extends Model
{
    protected $table = 'page';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'key',
        'header_image',
        'content',
        'additional',
        'type'
    ];

    protected $appends = [
        'header_image_full'
    ];

    public function getHeaderImageFullAttribute()
    {
        return strlen($this->header_image) > 0 ? asset('uploads/page/'.$this->header_image) : asset('assets/cms/images/no-img.png');
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['updated_at'])
            ->format('Y-m-d H:i:s');
    }

    public static function boot()
    {
        parent::boot();

        self::updated(function($model){
            Cache::forget('page_'.$model->key);
        });

        self::deleting(function($model){
            Cache::forget('page_'.$model->key);
        });
    }

}
