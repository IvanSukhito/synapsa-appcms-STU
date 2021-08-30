<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersCartDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_cart_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_cart_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->integer('qty')->default(0);
            $table->timestamps();
            $table->foreign('users_cart_id', 'userc_rel')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('product_id', 'proc_rel')
                ->references('id')->on('product')
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
        Schema::dropIfExists('users_cart_detail');
    }
}
