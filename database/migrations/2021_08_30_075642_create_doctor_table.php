<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('doctor_category_id')->default(0);
            $table->text('formal_edu')->nullable();
            $table->text('nonformal_edu')->nullable();
            $table->foreign('doctor_category_id', 'docat_rel')
            ->references('id')->on('doctor_category')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->foreign('user_id', 'docuser_rel')
            ->references('id')->on('users')
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
        Schema::dropIfExists('doctor');
    }
}
