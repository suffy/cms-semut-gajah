<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->integerIncrements('id');
            $table->string('invoice');
            $table->integer('customer_id');
            $table->string('subscribe_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_link')->nullable();
            $table->datetime('payment_date')->nullable();
            $table->double('payment_total', 65, 2)->nullable();
            $table->integer('coupon_id')->nullable();
            $table->string('payment_discount_code')->nullable();
            $table->double('payment_discount', 65, 2)->nullable();
            $table->string('payment_code')->nullable();
            $table->string('order_weight')->nullable();
            $table->string('order_distance')->nullable();
            $table->integer('delivery_status')->nullable();
            $table->string('delivery_fee')->nullable();
            $table->string('delivery_track')->nullable();
            $table->string('delivery_time')->nullable();
            $table->date('delivery_date')->nullable();
            $table->datetime('order_time')->nullable();
            $table->datetime('confirmation_time')->nullable();
            $table->datetime('complete_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->string('status_faktur')->nullable();
            $table->integer('status_complaint')->nullable();
            $table->integer('status_review')->nullable();
            $table->integer('status_partner')->nullable();
            $table->string('site_code')->nullable();
            $table->string('complaint_id')->nullable();
            $table->datetime('review_at')->nullable();
            $table->double('point')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
