<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->text('content')->nullable();
            $table->text('content_en')->nullable();
            $table->text('icon')->nullable();
            $table->integer('category_id')->nullable();
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
        Schema::dropIfExists('features');
    }
}
