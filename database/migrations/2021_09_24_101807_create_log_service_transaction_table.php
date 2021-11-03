<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogServiceTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_service_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->default(0);
            $table->unsignedBigInteger('transaction_id')->default(0);
            $table->string('transaction_refer_id')->nullable();
            $table->string('service')->nullable();
            $table->string('type_payment')->nullable();
            $table->string('type_transaction')->nullable();
            $table->longText('params')->nullable();
            $table->longText('results')->nullable();
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
        Schema::dropIfExists('log_service_transaction');
    }
}
