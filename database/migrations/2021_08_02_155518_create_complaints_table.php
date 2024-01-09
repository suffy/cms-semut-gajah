<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('order_id')->unsigned()->nullable();
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->integer('brand_id')->unsigned()->nullable();
            $table->foreign('brand_id')
                ->references('id')->on('products')
                ->onDelete('cascade');
            $table->string('status')->nullable();
            $table->integer('half')->nullable();
            $table->string('qty')->nullable();
            $table->string('option')->nullable();
            $table->string('responded')->nullable();
            $table->timestamp('confirm_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('send_at')->nullable();
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
        Schema::dropIfExists('complaints');
    }
}
