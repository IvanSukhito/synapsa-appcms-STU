<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klinik_id')->default(0);
            $table->unsignedBigInteger('banner_category_id')->default(0);
            $table->string('title')->nullable();
            $table->text('image')->nullable();
            $table->text('target')->nullable();
            $table->datetime('time_start')->nullable();
            $table->datetime('time_end')->nullable();
            $table->integer('orders')->default(1);
            $table->tinyInteger('status')->default(0);
            $table->foreign('banner_category_id', 'bancat_rel')
                ->references('id')->on('banner_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
        Schema::dropIfExists('sliders');
    }
}
