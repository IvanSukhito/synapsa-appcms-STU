<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->unsignedBigInteger('sub_district_id')->default(0);
            $table->unsignedBigInteger('district_id')->default(0);
            $table->unsignedBigInteger('city_id')->default(0);
            $table->string('nik')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('address')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('zipCode')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->integer('gender')->default(1);
            $table->text('upload_ktp')->default(1);
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
        Schema::dropIfExists('users');
    }
}
