<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_location', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->nullable()
                ->references('id')->on('products')
                ->onDelete('cascade');
            $table->integer('location_id')->unsigned();
            $table->foreign('location_id')->nullable()
                ->references('id')->on('locations')
                ->onDelete('cascade');
            $table->double('price_sell')->nullable();
            $table->double('price_agent')->nullable();
            $table->double('price_promo')->nullable();
            $table->double('cashback1')->nullable();
            $table->double('cashback2')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('sold')->nullable();
            $table->integer('status')->nullable();
            $table->integer('admin_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_location');
    }
}
