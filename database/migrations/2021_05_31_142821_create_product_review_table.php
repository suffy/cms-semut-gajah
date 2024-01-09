<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_review', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->integer('product_id')->unsigned();
            $table->integer('user_id')->nullable();
            $table->integer('order_id')->unsigned();
            $table->integer('parent_id')->nullable();
            $table->string('type', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->double('star_review')->nullable();
            $table->text('detail_review')->nullable();
            $table->text('category_review')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('product_id')->nullable()
                ->references('id')->on('products')
                ->onDelete('cascade');
            $table->foreign('order_id')->nullable()
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
        Schema::dropIfExists('product_review');
    }
}
