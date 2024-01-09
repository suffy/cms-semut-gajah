<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_address', function (Blueprint $table) {

                $table->integerIncrements('id');
                $table->integer('user_id')->nullable();
                $table->integer('mapping_site_id')->nullable();
                $table->string('name')->nullable();
                $table->string('shop_name')->nullable();
                $table->string('address_name')->nullable();
                $table->string('address_phone')->nullable();
                $table->string('address')->nullable();
                $table->string('kelurahan')->nullable();
                $table->string('kecamatan')->nullable();
                $table->string('kota')->nullable();
                $table->string('provinsi')->nullable();
                $table->string('kode_pos')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('default_address')->nullable();
                $table->string('status')->nullable();
                $table->string('type')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->datetime('deleted_at')->nullable();
    
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_address');
    }
}
