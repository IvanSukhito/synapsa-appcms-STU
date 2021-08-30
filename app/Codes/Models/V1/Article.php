<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'tagging';
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
        'created_by',
        'updated_by'
    ];

    public function getCategory()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id', 'id');
    }

    public function getTagging()
    {
        return $this->belongsToMany(Tagging::class, 'article_tagging', 'article_id', 'tagging_id');
    }

}
