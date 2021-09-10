<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->unsignedBigInteger('service_id')->default(0);
            $table->date('date_available')->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->integer('book')->default(0);
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
        Schema::dropIfExists('doctor_schedule');
    }
}
