<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('province_id')->default(0);
            $table->unsignedBigInteger('city_id')->default(0);
            $table->unsignedBigInteger('district_id')->default(0);
            $table->unsignedBigInteger('sub_district_id')->default(0);
            $table->string('address_name')->nullable();
            $table->string('address')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('zip_code')->nullable();
            $table->timestamps();
            $table->index(['id', 'user_id'], 'users_add_in');
            $table->foreign('user_id', 'users_add_rel')
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
        Schema::dropIfExists('users_address');
    }
}
