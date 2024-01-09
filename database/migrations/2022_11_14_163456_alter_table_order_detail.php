<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOrderDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('order_detail', function (Blueprint $table) {
            $table->double('qty_update')->default('0');
            $table->double('qty_cancel')->default('0');
            $table->double('total_price_update')->default('0');
            $table->double('total_price_cancel')->default('0');
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
