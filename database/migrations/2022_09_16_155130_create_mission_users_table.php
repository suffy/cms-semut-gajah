<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mission_id');
            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        
            $table->integer('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('mission_status')->nullable();

            $table->index(['mission_id', 'user_id'])->unique(); 

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
        Schema::dropIfExists('mission_users');
    }
}
