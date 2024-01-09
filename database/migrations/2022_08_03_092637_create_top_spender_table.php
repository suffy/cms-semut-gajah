<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopSpenderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_spender', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('title')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->string('site_code')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('reward')->nullable();
            $table->integer('limit')->nullable();
            $table->integer('product_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('top_spender');
    }
}
