<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->longText('diagnosis')->nullable();
            $table->longText('medication')->nullable();
            $table->longText('upload_rescipe')->nullable();
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
        Schema::dropIfExists('consultation');
    }
}
