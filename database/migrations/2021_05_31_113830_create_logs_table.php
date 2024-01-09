<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->datetime('log_time')->nullable();
            $table->string('activity')->nullable();
            $table->string('table_id')->nullable();
            $table->string('table_name')->nullable();
            $table->string('column_name')->nullable();
            $table->integer('from_user')->nullable();
            $table->integer('to_user')->nullable();
            $table->text('data_content')->nullable();
            $table->string('platform')->nullable();
            $table->integer('user_seen')->nullable();
            $table->integer('admin_seen')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('logs');
    }
}
