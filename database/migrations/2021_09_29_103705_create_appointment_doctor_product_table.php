<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentDoctorProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_doctor_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_doctor_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->string('product_name')->default(0);
            $table->integer('product_qty')->default(0);
            $table->decimal('product_price', 26, 2)->default(0);
            $table->tinyInteger('choose')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_doctor_product');
    }
}
