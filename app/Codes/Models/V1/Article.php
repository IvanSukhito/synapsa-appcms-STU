<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'article';
    protected $primaryKey = 'id';
    protected $fillable = [
        'article_category_id',
        'title',
        'slugs',
        'thumbnail_img',
        'image',
        'content',
        'preview',
        'publish_status',
        'publish_date',
        'created_by',
        'updated_by'
    ];

    protected $appends = [
        'image_full',
        'thumbnail_img_full'
    ];

    public function getCategory()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'article_tagging', 'article_id', 'tagging_id');
    }

    public function getImageFullAttribute()
    {
       
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
        //return strlen($this->image) > 0 ? asset($this->image) : asset('assets/cms/images/no-img.png');
    }
    public function getThumbnailImgFullAttribute()
    {
       
        if (strlen($this->thumbnail_img) > 0) {
            return env('OSS_URL').'/'.$this->thumbnail_img;
        }
        return asset('assets/cms/images/no-img.png');
        //return strlen($this->image) > 0 ? asset($this->image) : asset('assets/cms/images/no-img.png');
    }

}
