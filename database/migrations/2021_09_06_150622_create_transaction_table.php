<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('payment_id')->default(0);
            $table->unsignedBigInteger('shipping_id')->default(0);
            $table->string('payment_refer_id')->nullable();
            $table->string('payment_service')->nullable();
            $table->string('type_payment')->nullable();
            $table->string('code')->nullable();
            $table->string('payment_name')->nullable();
            $table->text('payment_detail')->nullable();
            $table->string('shipping_name')->nullable();
            $table->text('shipping_address_name')->nullable();
            $table->string('shipping_address')->nullable();
            $table->unsignedBigInteger('shipping_province_id')->default(0);
            $table->string('shipping_province_name')->nullable();
            $table->unsignedBigInteger('shipping_city_id')->default(0);
            $table->string('shipping_city_name')->nullable();
            $table->unsignedBigInteger('shipping_district_id')->default(0);
            $table->string('shipping_district_name')->nullable();
            $table->unsignedBigInteger('shipping_subdistrict_id')->default(0);
            $table->string('shipping_subdistrict_name')->nullable();
            $table->string('shipping_zipcode')->nullable();
            $table->decimal('shipping_price', 26, 2)->nullable();
            $table->string('total_qty')->nullable();
            $table->decimal('subtotal', 26, 2)->nullable();
            $table->decimal('total', 26, 2)->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('receiver_address')->nullable();
            $table->unsignedBigInteger('category_service_id')->default(0);
            $table->string('category_service_name')->nullable();
            $table->tinyInteger('type_service')->default(0);
            $table->string('type_service_name')->nullable();
            $table->text('extra_info')->nullable();
            $table->text('send_info')->nullable();
            $table->longText('payment_info')->nullable();
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
        Schema::dropIfExists('transaction');
    }
}
