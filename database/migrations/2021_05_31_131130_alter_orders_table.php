<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('payment_final', 65, 2)->nullable();
            $table->string('photo', 300)->nullable();
            $table->string('courier', 255)->nullable();
            $table->string('delivery_service', 255)->nullable();
            $table->integer('coupon_id')->nullable()->unsigned()->change();
            $table->foreign('coupon_id')->references('id')->on('coupon')->onDelete('cascade');
            $table->integer('customer_id')->nullable()->unsigned()->change();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
