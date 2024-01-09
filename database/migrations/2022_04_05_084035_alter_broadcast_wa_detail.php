<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBroadcastWaDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('broadcast_wa_detail', function (Blueprint $table) {
            $table->integer('id_broadcast_wa')->nullable()->unsigned()->change();
            $table->foreign('id_broadcast_wa')->references('id')->on('broadcast_wa')->onDelete('cascade');
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
