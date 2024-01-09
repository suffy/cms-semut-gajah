<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->string('harga_ritel_gt')->nullable();
            $table->string('harga_grosir_mt')->nullable();
            $table->string('harga_semi_grosir')->nullable();
            $table->string('harga_promosi_coret_ritel_gt')->nullable();
            $table->string('harga_promosi_coret_grosir_mt')->nullable();
            $table->string('harga_promosi_coret_semi_grosir')->nullable();
            // update 21 - 09 - 21
            // $table->string('medium_retail')->nullable(); (ritel_gt)
            // $table->string('medium_grosir')->nullable(); (grosir_mt)
            // $table->string('small_retail')->nullable();
            // $table->string('small_grosir')->nullable();
            // $table->string('small_unit')->nullable();
            // $table->string('price_apps')->nullable();
            // $table->string('price_promo')->nullable();
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
        Schema::dropIfExists('product_prices');
    }
}
