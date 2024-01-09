<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helps', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('help_category_id')->unsigned()->nullable();
            $table->foreign('help_category_id')
                ->references('id')->on('help_categories')
                ->onDelete('cascade');
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
        Schema::dropIfExists('helps');
    }
}
