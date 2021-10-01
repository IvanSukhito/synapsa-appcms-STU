<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentLabDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_lab_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_lab_id')->default(0);
            $table->unsignedBigInteger('lab_id')->default(0);
            $table->string('lab_name')->nullable();
            $table->text('test_results')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->foreign('appointment_lab_id', 'al1')
                ->references('id')->on('appointment_lab')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_lab_details');
    }
}
