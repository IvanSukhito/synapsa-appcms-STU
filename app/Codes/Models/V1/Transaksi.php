<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaction';
    protected $primaryKey = 'id';
    protected $fillable = [
      'users_id',
      'payment',
      'payment_code',
      'total_price',
      'customer_name',
      'customer_address',
      'shipping',
      'list_order',
      'status'
    ];
    protected $appends = [
      'status_transaction'
  ];

  public function getStatusTransactionAttribute()
  {
      $getList = get_list_transaction();
      return $getList[$this->status] ?? $this->status;
  }





}
