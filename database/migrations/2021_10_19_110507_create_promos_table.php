<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->text('highlight')->nullable();
            $table->string('point')->nullable();
            $table->string('termcondition')->nullable();
            $table->string('detail_termcondition')->nullable();
            $table->string('category')->nullable();
            $table->string('detail_category')->nullable();
            $table->string('contact')->nullable();
            $table->string('report')->nullable();
            $table->string('banner')->nullable();
            $table->string('multiple')->nullable();
            $table->string('reward_choose')->nullable();
            $table->string('status')->nullable();
            $table->integer('min_qty')->nullable();
            $table->integer('min_sku')->nullable();
            $table->integer('min_transaction')->nullable();
            $table->integer('all_transaction')->nullable();
            $table->string('class_cust')->nullable();
            $table->string('type_cust')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->integer('special')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('promos');
    }
}
