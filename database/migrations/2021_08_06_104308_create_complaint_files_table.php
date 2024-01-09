<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_files', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('complaint_id')->unsigned()->nullable();
            $table->foreign('complaint_id')
                ->references('id')->on('complaints')
                ->onDelete('cascade');
            $table->integer('complaint_detail_id')->unsigned()->nullable();
            $table->foreign('complaint_detail_id')
                ->references('id')->on('complaint_details')
                ->onDelete('cascade');
            $table->string('file_1')->nullable();
            $table->string('file_2')->nullable();
            $table->string('file_3')->nullable();
            $table->string('file_4')->nullable();
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
        Schema::dropIfExists('complaint_files');
    }
}
