<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id');
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->text('content')->nullable();
            $table->text('content_en')->nullable();
            $table->string('slug')->nullable();
            $table->string('post_type')->nullable();
            $table->string('position')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('post_parent')->nullable();
            $table->integer('menu_order')->nullable();
            $table->text('tags')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('pages');
    }
}
