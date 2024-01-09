<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCreditHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->integer('credit_id')->nullable()->unsigned()->change();
            $table->foreign('credit_id')->references('id')->on('credits')->onDelete('cascade');
            $table->integer('order_id')->nullable()->unsigned()->change();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
