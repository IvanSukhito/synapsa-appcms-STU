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

}
