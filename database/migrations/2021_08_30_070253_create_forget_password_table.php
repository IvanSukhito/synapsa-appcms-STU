<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForgetPasswordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forget_password', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id')->default(0);
            $table->string('email')->nullable();
            $table->string('code')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('attempt')->nullable();
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
        Schema::dropIfExists('forget_password');
    }
}
