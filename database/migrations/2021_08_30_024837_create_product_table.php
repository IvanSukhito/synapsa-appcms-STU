<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_category_id')->default(0);
            $table->string('sku')->nullable();
            $table->string('name')->nullable();
            $table->text('image')->nullable();
            $table->decimal('price', 26, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('desc')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_flag')->default(0);
            $table->timestamps();
            $table->foreign('product_category_id', 'procat_rel')
                ->references('id')->on('product_category')
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
        Schema::dropIfExists('product');
    }
}
