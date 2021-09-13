<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_service', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->default(0);
            $table->unsignedBigInteger('lab_id')->default(0);
            $table->decimal('price', 26, 2)->default(0);
            $table->foreign('service_id', 'ls1_rel')
                ->references('id')->on('service')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('lab_id', 'ls2_rel')
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
        Schema::dropIfExists('lab_service');
    }
}
