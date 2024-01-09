<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('kodeprod')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('brand')->nullable();
            $table->string('status_herbana')->nullable();
            $table->string('invoice_name')->nullable();
            $table->string('name')->nullable();
            $table->string('search_name')->nullable();
            // $table->string('large_unit')->nullable();
            // $table->string('medium_unit')->nullable();
            // $table->string('small_unit')->nullable();
            $table->string('satuan_online')->nullable(); // update 21-09-21
            // $table->string('large_qty')->nullable();
            // $table->string('medium_qty')->nullable();
            // $table->string('small_qty')->nullable();
            $table->string('group_id')->nullable();
            $table->string('nama_group')->nullable();
            $table->string('subgroup')->nullable();
            $table->string('nama_sub_group')->nullable();
            $table->string('besar')->nullable();
            $table->string('sedang')->nullable();
            $table->string('kecil')->nullable();
            $table->string('qty1')->nullable();
            $table->string('qty2')->nullable();
            $table->string('qty3')->nullable();
            $table->string('konversi_sedang_ke_kecil')->nullable(); // update 21-09-21
            $table->string('min_pembelian')->nullable(); // update 21-09-21
            $table->string('price_sell')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable();  
            $table->string('sku')->nullable();
            $table->string('image')->nullable(); // update 12-01-2022
            $table->integer('spent')->nullable(); // update 12-01-2022
            $table->integer('point')->nullable(); // update 12-01-2022
            $table->double('ratio')->nullable();
            $table->string('type')->nullable();
            // $table->string('brand')->nullable();
            $table->double('price_buy')->nullable();
            $table->double('price_promo')->nullable();
            $table->string('cashback_1')->nullable();
            $table->string('cashback_2')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('available')->nullable();
            // $table->double('weight')->nullable();
            $table->boolean('featured')->nullable();
            $table->integer('menu_order')->nullable();
            $table->integer('admin_id')->nullable();
            $table->double('rate_val')->nullable();
            $table->integer('rate_count')->nullable();
            $table->integer('sale_counts')->nullable();
            $table->integer('view_counts')->nullable();
            $table->text('tags')->nullable();
            $table->integer('status')->nullable();
            $table->integer('status_renceng')->nullable();
            $table->integer('status_promosi_coret')->nullable();
            $table->integer('status_terlaris')->nullable();
            $table->integer('status_terbaru')->nullable();
            $table->integer('status_partner')->nullable();
            $table->integer('status_renceng')->nullable();
            $table->integer('status_redeem')->nullable();
            $table->integer('redeem_point')->nullable();
            $table->text('redeem_desc')->nullable();
            $table->text('redeem_snk')->nullable();
            $table->string('site_code')->nullable();
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
        Schema::dropIfExists('products');
    }
}
