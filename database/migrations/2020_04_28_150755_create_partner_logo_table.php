<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerLogoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_logo', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('url')->nullable();
            $table->string('type')->nullable();
            $table->text('content')->nullable();
            $table->string('icon')->nullable();
            $table->string('menu_order')->nullable();
            $table->integer('status');
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
        Schema::dropIfExists('partner_logo');
    }
}
