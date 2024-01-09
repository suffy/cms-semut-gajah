<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMappingSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapping_site', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('kode')->unique();
            $table->string('branch_name')->nullable();
            $table->string('nama_comp')->nullable();
            $table->string('kode_comp')->nullable();
            $table->string('sub')->nullable();
            $table->string('status_ho')->nullable();
            $table->string('telp_wa')->nullable();
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
        Schema::dropIfExists('mapping_site');
    }
}
