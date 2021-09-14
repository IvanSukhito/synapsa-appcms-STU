<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('lab_id')->default(0);
            $table->unsignedBigInteger('service_id')->default(0);
            $table->timestamps();
            $table->foreign('user_id', 'lc1_rel')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('lab_id', 'lc2_rel')
                ->references('id')->on('lab')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('service_id', 'lc3_rel')
                ->references('id')->on('service')
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
        Schema::dropIfExists('lab_cart');
    }
}
