<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_verifications', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id')->nullable();
            $table->integer('status')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->datetime('checked_at')->nullable();
            $table->datetime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_verifications');
    }
}
