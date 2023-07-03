<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('country_code')->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->string('picture')->nullable();
            $table->string('language',10)->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->tinyInteger('is_bankdetail')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('zone_id')->nullable();
            $table->double('wallet_balance', 15, 2)->default(0);
            $table->string('company_name')->nullable();
            $table->float('commision')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->enum('type', ['ADMIN','DISPATCHER','FLEET','ACCOUNT','DISPUTE']);
            $table->unsignedInteger('company_id');
            $table->enum('created_type', ['ADMIN'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
