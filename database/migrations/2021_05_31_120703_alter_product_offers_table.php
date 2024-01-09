<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_offers', function (Blueprint $table) {
            $table->dropForeign('product_offers_product_id_foreign');
            $table->dropColumn('product_id');
            $table->dropColumn('percentage');
            $table->dropColumn('price');
            $table->dropColumn('quantity');
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('location')->nullable();
            $table->string('icon', 255)->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_offers', function (Blueprint $table) {
            //
        });
    }
}
