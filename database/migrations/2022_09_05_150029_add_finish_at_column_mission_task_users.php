<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinishAtColumnMissionTaskUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_task_users', function (Blueprint $table) {
            $table->timestamp('finish_at')->nullable();
            $table->timestamp('send_reward_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_task_users', function (Blueprint $table) {
            //
        });
    }
}
