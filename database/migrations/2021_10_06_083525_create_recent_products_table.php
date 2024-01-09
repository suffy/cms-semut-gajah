<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecentProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recent_products', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->datetime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recent_products');
    }
}
