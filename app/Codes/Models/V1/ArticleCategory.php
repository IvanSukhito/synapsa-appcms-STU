<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    protected $table = 'article_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

    public function getArticle()
    {
        return $this->hasMany(Article::class, 'article_category_id', 'id');
    }

}
