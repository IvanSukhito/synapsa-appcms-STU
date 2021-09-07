<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJanjiTemuDoctorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('janji_temu_doctor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->date('book_day')->nullable();
            $table->time('book_at')->nullable();
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
        Schema::dropIfExists('janji_temu_doctor');
    }
}
