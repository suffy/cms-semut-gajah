<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPromoDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promo_rewards', function (Blueprint $table) {
            $table->integer('promo_id')->nullable()->unsigned()->change();
            $table->foreign('promo_id')->references('id')->on('promos')->onDelete('cascade');
            $table->integer('reward_product_id')->nullable()->unsigned()->change();
            $table->foreign('reward_product_id')->references('id')->on('products')->onDelete('cascade');
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
