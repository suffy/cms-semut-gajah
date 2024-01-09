<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_availability', function (Blueprint $table) {
            $table->string('site_code')->nullable()->unsigned()->change();
            $table->foreign('site_code')->references('kode')->on('mapping_site')->onDelete('cascade');
            $table->integer('product_id')->nullable()->unsigned()->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
