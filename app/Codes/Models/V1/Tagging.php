<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Tagging extends Model
{
    protected $table = 'tagging';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

    public function getArticle()
    {
        return $this->belongsToMany(Article::class, 'article_tagging', 'tagging_id', 'article_id');
    }

    public function getProduct()
    {
        return $this->belongsToMany(Product::class, 'product_tagging', 'tagging_id', 'product_id');
    }

}
