<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->string('name')->nullable();
            $table->text('image')->nullable();
            $table->text('desc_lab')->nullable();
            $table->text('desc_benefit')->nullable();
            $table->text('desc_preparation')->nullable();
            $table->text('recommended_for')->nullable();
            $table->tinyInteger('priority')->default(0);
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
        Schema::dropIfExists('lab');
    }
}
