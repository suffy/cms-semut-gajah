<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionTaskUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_task_users', function (Blueprint $table) {
            $table->unsignedBigInteger('mission_task_id');
            $table->foreign('mission_task_id')->references('id')->on('mission_tasks')->onDelete('cascade');
        
            $table->integer('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('status')->nullable();

            $table->index(['mission_task_id', 'user_id'])->unique(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_task_users');
    }
}
