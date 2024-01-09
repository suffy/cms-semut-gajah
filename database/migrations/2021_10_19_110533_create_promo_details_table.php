<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_rewards', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('promo_id')->nullable();
            $table->string('reward_disc')->nullable();
            $table->integer('reward_product_id')->nullable();
            $table->string('reward_qty')->nullable();
            $table->string('reward_nominal')->nullable();
            $table->string('reward_point')->nullable();
            $table->string('max')->nullbable();
            $table->string('satuan')->nullable();
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
        Schema::dropIfExists('promo_details');
    }
}
