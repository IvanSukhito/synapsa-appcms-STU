<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_id')->default(0);
            $table->date('book_date')->nullable();
            $table->string('book_at')->nullable();
            $table->integer('orders')->default(1);
            $table->timestamps();
            $table->foreign('lab_id', 'lab1_rel')
            ->references('id')->on('lab')
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
        Schema::dropIfExists('lab_schedule');
    }
}
