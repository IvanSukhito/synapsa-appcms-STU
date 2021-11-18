<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->default(0);
            $table->unsignedBigInteger('product_category_id')->default(0);
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->string('product_category_name')->nullable();
            $table->string('klinik_name')->nullable();
            $table->text('klinik_address')->nullable();
            $table->string('klinik_no_telp')->nullable();
            $table->string('klinik_email')->nullable();
            $table->string('product_name')->nullable();
            $table->text('product_image')->nullable();
            $table->decimal('price_product_klinik', 26, 2)->nullable();
            $table->decimal('price_product_synapsa', 26, 2)->nullable();
            $table->string('product_unit')->nullable();
            $table->tinyInteger('product_type')->default(0);
            $table->datetime('transaction_date')->nullable();
            $table->string('total_qty_transaction')->nullable();
            $table->decimal('total_price_transaction', 26, 2)->nullable();
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('invoice');
    }
}
