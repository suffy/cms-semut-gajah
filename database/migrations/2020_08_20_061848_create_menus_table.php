<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->integer('menu_order')->nullable();
            $table->integer('status')->nullable();
            $table->integer('editable')->nullable();
            $table->integer('deletable')->nullable();
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
        Schema::dropIfExists('menus');
    }
}
