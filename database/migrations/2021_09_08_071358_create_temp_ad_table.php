<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempAdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_ad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id')->default(0);
            $table->text('detail_address')->nullable();
            $table->text('detail_shipping')->nullable();
            $table->text('detail_information')->nullable();
      
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
        Schema::dropIfExists('temp_ad');
    }
}
