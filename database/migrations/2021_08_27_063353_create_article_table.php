<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_category_id')->default(0);
            $table->string('title')->nullable();
            $table->string('slugs')->nullable();
            $table->text('thumbnail_img')->nullable();
            $table->text('image')->nullable();
            $table->text('content')->nullable();
            $table->string('preview')->nullable();
            $table->tinyInteger('publish_status')->default(1);
            $table->date('publish_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('article_category_id', 'arcat_rel')
                ->references('id')->on('article_category')
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
        Schema::dropIfExists('article');
    }
}
