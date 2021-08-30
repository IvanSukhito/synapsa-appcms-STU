<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubDistrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_district', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id')->default(0);
            $table->string('name')->nullable();
            $table->timestamps();
            $table->foreign('district_id', 'subdistrict_rel')
                ->references('id')->on('district')
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
        Schema::dropIfExists('sub_district');
    }
}
