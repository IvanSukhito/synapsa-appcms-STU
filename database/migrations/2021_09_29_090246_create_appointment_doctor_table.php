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
            $table->unsignedBigInteger('schedule_id')->default(0);
            $table->unsignedBigInteger('service_id')->default(0);
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->tinyInteger('type_appointment')->default(1);
            $table->date('date')->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->string('patient_name')->nullable();
            $table->string('patient_email')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('video_link')->nullable();
            $table->longText('form_patient')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->longText('doctor_prescription')->nullable();
            $table->longText('extra_info')->nullable();
            $table->tinyInteger('online_meeting')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->text('message')->nullable();
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
