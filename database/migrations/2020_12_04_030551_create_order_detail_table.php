<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_detail', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('product_id')->nullable();
            $table->integer('order_id')->unsigned();
            // $table->double('large_price', 65, 2)->nullable();
            // $table->integer('large_qty')->nullable();
            $table->integer('large_unit')->nullable();
            // $table->double('medium_price', 65, 2)->nullable();
            // $table->integer('medium_qty')->nullable();
            $table->integer('medium_unit')->nullable();
            // $table->double('small_price', 65, 2)->nullable();
            // $table->integer('small_qty')->nullable();
            $table->integer('small_unit')->nullable();
            $table->string('konversi_sedang_ke_kecil')->nullable(); // update 21-09-21
            $table->integer('half')->nullable(); // update 11-03-22
            $table->string('qty_konversi')->nullable(); // update 21-09-21
            $table->integer('qty')->nullable();
            $table->double('price_apps', 65, 2)->nullable();
            $table->double('total_price', 65, 2)->nullable();
            $table->string('notes')->nullable();
            $table->string('product_review_id')->nullable();
            $table->integer('promo_id')->nullable();
            $table->string('disc_cabang')->nullable();
            $table->string('rp_cabang')->nullable();
            $table->string('disc_principal')->nullable();
            $table->string('rp_principal')->nullable();
            $table->string('point_principal')->nullable();
            $table->string('bonus')->nullable();
            $table->string('bonus_name')->nullable();
            $table->string('bonus_qty')->nullable();
            $table->string('bonus_konversi')->nullable();
            $table->double('point')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->foreign('order_id')
                                ->references('id')->on('orders')
                                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_detail');
    }
}
