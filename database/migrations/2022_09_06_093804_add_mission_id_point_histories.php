<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissionIdPointHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('mission_id')->nullable();

            // $table->unsignedBigInteger('mission_id')->nullable()->unsigned()->change();
            // $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            //
        });
    }
}
