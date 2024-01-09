<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_histories', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('credit_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('deposit')->nullable();
            $table->integer('kredit')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->datetime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_histories');
    }
}
