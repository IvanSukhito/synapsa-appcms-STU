<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Sliders extends Model
{
    protected $table = 'sliders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'klinik_id',
        'title',
        'image',
        'type',
        'target_url',
        'target_menu',
        'target_id',
        'time_start',
        'time_end',
        'orders',
        'status',
    ];

    protected $appends = [
        'image_full',
        'type_nice'
    ];

    public function getImageFullAttribute()
    {
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getTypeNiceAttribute()
    {
        $getList = get_list_sliders_type();
        return $getList[$this->type] ?? '';
    }
}
