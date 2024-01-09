<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id');
            $table->integer('post_category_id')->unsigned();
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->text('content')->nullable();
            $table->text('content_en')->nullable();
            $table->string('slug')->nullable();
            $table->string('post_type')->nullable();
            $table->string('featured_image')->nullable();
            $table->text('featured_video')->nullable();
            $table->text('video')->nullable();
            $table->integer('post_parent')->nullable();
            $table->integer('menu_order')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('keyword')->nullable();
            $table->text('keyword_en')->nullable();
            $table->text('tags')->nullable();
            $table->text('tags_en')->nullable();
            $table->integer('status')->nullable();
            $table->foreign('post_category_id')->nullable()
                ->references('id')->on('post_categories')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
