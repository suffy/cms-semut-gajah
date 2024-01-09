<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integerIncrements('id')->start_from(50000);
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('pin')->nullable();
            $table->string('phone')->nullable();
            $table->dateTime('phone_verified_at')->nullable();
            $table->integer('account_type')->nullable();
            $table->string('account_role')->nullable();
            $table->string('photo')->nullable();
            $table->double('credit_limit')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('account_status')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->string('code_approval')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->string('banner')->nullable();
            $table->string('kode_type')->nullable();
            $table->string('site_code')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('salesman_code')->nullable();
            $table->string('salesman_erp_code')->nullable();
            $table->string('point')->nullable();
            $table->string('badge')->nullable();
            $table->string('salur_code')->nullable();
            $table->string('class')->nullable();
            $table->string('type_payment')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE users AUTO_INCREMENT = 14000;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
