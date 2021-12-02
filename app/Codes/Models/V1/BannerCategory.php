<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class BannerCategory extends Model
{
    protected $table = 'banner_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'key',
        'name',
        'type',
        'status'
    ];

    protected $appends = [
        'type_nice'
    ];

    public function getTypeNiceAttribute()
    {
        $getList = get_list_banner_category_type();
        return $getList[$this->type] ?? '-';
    }
}
