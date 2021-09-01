<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_email', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('content')->nullable();
            $table->longText('response')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('error_message')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address')->nullable();
            $table->tinyInteger('trigger')->default(0);
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
        Schema::dropIfExists('log_email');
    }
}
