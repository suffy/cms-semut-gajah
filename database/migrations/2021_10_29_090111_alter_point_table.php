<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->integer('customer_id')->nullable()->unsigned()->change();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
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
