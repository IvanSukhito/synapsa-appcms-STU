<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->default(0);
            $table->longText('old_data')->nullable();
            $table->longText('new_data')->nullable();
            $table->text('reason')->nullable();
            $table->longText('additional_info')->nullable();
            $table->string('service')->nullable();
            $table->string('type_payment')->nullable();
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
        Schema::dropIfExists('log_transaction');
    }
}
