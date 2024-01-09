<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('customer_code')->nullable();
            $table->string('site_id')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('loper_id')->nullable();
            $table->string('loper')->nullable();
            $table->string('no_kendaraan')->nullable();
            $table->string('driver')->nullable();
            $table->string('periode')->nullable();
            $table->double('accuracy')->nullable();
            $table->timestamps();

            $table->string('customer_code')->nullable()->unique()->change();
            $table->foreign('customer_code')->references('customer_code')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
}
