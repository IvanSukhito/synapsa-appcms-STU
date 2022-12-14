<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTaggingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_tagging', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->default(0);
            $table->unsignedBigInteger('tagging_id')->default(0);
            $table->foreign('product_id', 'prod1_rel')
                ->references('id')->on('product')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('tagging_id', 'protag2_rel')
                ->references('id')->on('tagging')
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
        Schema::dropIfExists('product_tagging');
    }
}
