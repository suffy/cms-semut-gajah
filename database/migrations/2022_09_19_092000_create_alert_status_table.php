<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('alert_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->unsignedBigInteger('alert_id')->nullable()->unsigned()->change();
            $table->foreign('alert_id')->references('id')->on('alerts')->onDelete('cascade');
        
            $table->integer('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alert_status');
    }
}
