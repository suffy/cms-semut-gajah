`<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShoppingCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopping_cart', function (Blueprint $table) {
            $table->integer('status')->nullable();
            $table->integer('user_id')->nullable()->unsigned()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('product_id')->nullable()->unsigned()->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopping_cart', function (Blueprint $table) {
            //
        });
    }
}
