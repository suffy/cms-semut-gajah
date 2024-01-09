<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->integer('menu_id')->nullable();
            $table->text('icon')->nullable();
            $table->integer('menu_order')->nullable();
            $table->integer('status')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('product_categories');
    }
}
