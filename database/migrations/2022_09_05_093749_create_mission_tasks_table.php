<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mission_id')->nullable();
            $table->integer('type')->nullable(); // 1 untuk qty, 2 untuk total harga 
            $table->double('qty')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('group_id')->nullable();
            $table->string('subgroup')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unsignedBigInteger('mission_id')->nullable()->unsigned()->change();
            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_tasks');
    }
}
