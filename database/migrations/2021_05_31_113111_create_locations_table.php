<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->string('slug', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('icon', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->string('latitude', 255)->nullable();
            $table->integer('status')->nullable();
            $table->integer('ordering')->nullable();
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
        Schema::dropIfExists('locations');
    }
}
