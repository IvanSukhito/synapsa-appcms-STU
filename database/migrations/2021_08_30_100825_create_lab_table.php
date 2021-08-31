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
            $table->integer('parent_id')->default(0);
            $table->string('name')->nullable();
            $table->decimal('price', 26, 2)->nullable();
            $table->text('thumbnail_img')->nullable();
            $table->text('image')->nullable();
            $table->text('desc_lab')->nullable();
            $table->text('desc_benefit')->nullable();
            $table->text('desc_preparation')->nullable();
            $table->text('recommended_for')->nullable();
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
