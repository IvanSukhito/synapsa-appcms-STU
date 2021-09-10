<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->string('product_name')->nullable();
            $table->integer('product_qty')->default(0);
            $table->decimal('product_price', 26, 2)->default(0);
            $table->unsignedBigInteger('schedule_id')->default(0);
            $table->unsignedBigInteger('doctor_id')->default(0);
            $table->string('doctor_name')->nullable();
            $table->decimal('doctor_price', 26, 2)->default(0);
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
        Schema::dropIfExists('transaction_details');
    }
}
