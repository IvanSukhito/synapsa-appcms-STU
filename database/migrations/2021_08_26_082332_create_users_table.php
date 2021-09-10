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
            $table->id();
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->unsignedBigInteger('city_id')->default(0);
            $table->unsignedBigInteger('district_id')->default(0);
            $table->unsignedBigInteger('sub_district_id')->default(0);
            $table->unsignedBigInteger('interest_service_id')->default(0);
            $table->unsignedBigInteger('interest_category_id')->default(0);
            $table->string('fullname')->nullable();
            $table->string('address')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('zip_code')->nullable();
            $table->date('dob')->nullable();
            $table->integer('gender')->default(1);
            $table->string('nik')->nullable();
            $table->text('upload_ktp')->nullable();
            $table->text('image')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('patient')->default(0);
            $table->tinyInteger('doctor')->default(0);
            $table->tinyInteger('nurse')->default(0);
            $table->tinyInteger('verification_phone')->default(0);
            $table->tinyInteger('verification_email')->default(0);
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
        Schema::dropIfExists('users');
    }
}
