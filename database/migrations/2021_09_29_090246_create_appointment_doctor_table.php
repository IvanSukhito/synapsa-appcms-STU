<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentDoctorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_doctor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id')->default(0);
            $table->string('video_link')->nullable();
            $table->longText('form_patient')->nullable();
            $table->longText('diagnosis')->nullable();
            $table->longText('list_product')->nullable();
            $table->longText('list_rescipe')->nullable();
            $table->longText('extra_info')->nullable();
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
        Schema::dropIfExists('appointment_doctor');
    }
}
