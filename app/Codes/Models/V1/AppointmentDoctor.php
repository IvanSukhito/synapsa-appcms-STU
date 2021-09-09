<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class AppointmentDoctor extends Model
{
    protected $table = 'appointment_doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
      'doctor_id',
      'book_day',
      'book_at',
    ];

    public function cartDetails()
    {
        return $this->hasMany(UsersCartDetail::class, 'users_cart_id', 'id');
    }

}
