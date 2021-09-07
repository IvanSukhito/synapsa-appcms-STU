<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $table = 'transaction_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'product_qty',
        'product_price'
    ];

    protected $appends = [
        'status_transaction'
    ];

    public function getTransaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

}
