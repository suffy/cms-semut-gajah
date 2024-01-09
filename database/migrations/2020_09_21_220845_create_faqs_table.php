<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->text('icon')->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->string('position')->nullable();
            $table->text('question')->nullable();
            $table->text('question_en')->nullable();
            $table->text('answer')->nullable();
            $table->text('answer_en')->nullable();
            $table->text('data')->nullable();
            $table->integer('editable')->nullable();
            $table->integer('menu_order')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('faqs');
    }
}
