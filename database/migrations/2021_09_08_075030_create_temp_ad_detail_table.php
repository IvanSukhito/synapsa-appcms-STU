<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempAdDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_ad_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temp_ad_id')->default(0);
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->unsignedBigInteger('appointment_doctor_id')->default(0);
            $table->integer('qty')->default(0);
            $table->integer('choose')->default(0);
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
        Schema::dropIfExists('temp_ad_detail');
    }
}
