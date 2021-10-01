<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentLabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_lab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->tinyInteger('type_appointment')->default(1);
            $table->string('patient_name')->nullable();
            $table->string('patient_email')->nullable();
            $table->date('date')->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->longText('form_patient')->nullable();
            $table->integer('total_test')->default(0);
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
        Schema::dropIfExists('appointment_lab');
    }
}
