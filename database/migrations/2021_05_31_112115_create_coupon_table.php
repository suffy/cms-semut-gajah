<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon', function (Blueprint $table) {
            $table->integerIncrements('id'); 
            $table->string('code', 25)->nullable();
            $table->string('file')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('percent', 10)->nullable();
            $table->double('nominal')->nullable();
            $table->double('max_nominal')->nullable();
            $table->integer('max_use')->nullable();
            $table->integer('max_use_user')->nullable();
            $table->integer('daily_use')->nullable();
            $table->integer('used')->nullable();
            $table->double('min_transaction')->nullable();
            $table->double('max_transaction')->nullable();
            $table->string('category', 25)->nullable();
            $table->text('description')->nullable();
            $table->text('termandcondition')->nullable();
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->integer('admin_id')->unsigned();;
            $table->integer('is_public')->nullable();
            $table->integer('related_id')->nullable();
            $table->string('icon', 245)->nullable();
            $table->integer('is_new_user')->nullable();
            $table->integer('is_old_user')->nullable();
            $table->integer('is_expired')->nullable();
            $table->text('location')->nullable();
            $table->integer('available')->nullable();
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
        Schema::dropIfExists('coupon');
    }
}
