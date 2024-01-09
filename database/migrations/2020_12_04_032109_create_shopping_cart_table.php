<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_cart', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('promo_id')->nullable();
            // $table->string('large_retail')->nullable();
            // $table->string('large_grosir')->nullable();
            // $table->string('large_qty')->nullable();
            // $table->string('large_unit')->nullable();
            // $table->string('medium_retail')->nullable();
            // $table->string('medium_grosir')->nullable();
            // $table->string('medium_qty')->nullable();
            // $table->string('medium_unit')->nullable();
            // $table->string('small_retail')->nullable();
            // $table->string('small_grosir')->nullable();
            // $table->string('small_qty')->nullable();
            // $table->string('small_unit')->nullable();
            $table->string('satuan_online')->nullable();
            $table->string('konversi_sedang_ke_kecil')->nullable();
            $table->string('min_pembelian')->nullable();
            $table->integer('half')->nullable();
            $table->string('qty_konversi')->nullable();
            $table->string('qty')->nullable();
            $table->double('price_apps')->nullable();
            $table->double('total_price')->nullable();
            $table->double('order_disc')->nullable();
            $table->double('disc_cabang')->nullable();
            $table->string('notes')->nullable();
            $table->text('session')->nullable();
            $table->text('data')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
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
        Schema::dropIfExists('shopping_cart');
    }
}
