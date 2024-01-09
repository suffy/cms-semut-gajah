<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('page')->nullable();
            $table->string('images')->nullable();
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->string('link')->nullable();
            $table->text('banner_desc')->nullable();
            $table->text('banner_desc_en')->nullable();
            $table->integer('menu_order')->nullable();
            $table->integer('status')->nullable();
            $table->string('position')->nullable();
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
        Schema::dropIfExists('banners');
    }
}
