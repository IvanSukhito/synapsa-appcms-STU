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
            $table->string('email')->nullable();
            $table->string('code_verification')->nullable();
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
