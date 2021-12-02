<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string("type")->nullable();
            $table->string("target_menu")->nullable();
            $table->string("target_id")->nullable();
            $table->tinyInteger('is_read')->default(1);
            $table->datetime('date')->nullable();
            $table->timestamps();
            $table->foreign('user_id', 'users_not_rel')
                ->references('id')->on('users')
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
        Schema::dropIfExists('notifications');
    }
}
