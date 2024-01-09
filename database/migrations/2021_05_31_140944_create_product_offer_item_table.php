<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOfferItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_offer_item', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->integer('product_offer_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('views')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('sold')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('product_offer_id')->nullable()
                ->references('id')->on('product_offers')
                ->onDelete('cascade');
            $table->foreign('product_id')->nullable()
                ->references('id')->on('products')
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
        Schema::dropIfExists('product_offer_item');
    }
}
