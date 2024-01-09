<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_offers', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('product_id')->unsigned();
            $table->dateTime('day_start');
            $table->dateTime('day_end');
            $table->double('percentage', 2, 2);
            $table->double('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->foreign('product_id')->nullable()
                ->references('id')->on('products')
                ->onDelete('cascade');
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
        Schema::dropIfExists('product_offers');
    }
}
