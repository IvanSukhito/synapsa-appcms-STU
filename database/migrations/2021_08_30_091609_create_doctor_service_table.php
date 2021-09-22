<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_service', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->default(0);
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->decimal('price', 26, 2)->default(0);
            $table->foreign('service_id', 'serv1_rel')
                ->references('id')->on('service')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('doctor_id', 'doct2_rel')
                ->references('id')->on('doctor')
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
        Schema::dropIfExists('doctor_service');
    }
}
