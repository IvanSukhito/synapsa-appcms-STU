<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ArticleTagging extends Model
{
    protected $table = 'article_tagging';
    protected $primaryKey = 'id';
    protected $fillable = [
        'article_id',
        'tagging_id',
    ];




}
