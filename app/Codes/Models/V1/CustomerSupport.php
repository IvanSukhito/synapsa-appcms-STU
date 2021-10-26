<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class CustomerSupport extends Model
{
    protected $table = 'customer_support';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'type',
        'contact',
        'status'
    ];

    protected $appends = [
    'status_nice',
    'type_nice',
    ];

    public function getStatusNiceAttribute()
    {
        $getList = get_list_active_inactive();
        return $getList[$this->status] ?? $this->status;
    }

    public function getTypeNiceAttribute()
    {
        $getList = get_list_type_support();
        return $getList[$this->type] ?? $this->type;
    }

}
