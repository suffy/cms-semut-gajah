<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->string('icon')->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->integer('category_parent')->nullable();
            $table->integer('menu_order')->nullable();
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
        Schema::dropIfExists('post_categories');
    }
}
