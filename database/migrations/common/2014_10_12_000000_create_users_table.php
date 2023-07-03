<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('payment_mode')->default('CASH');
            $table->enum('user_type', ['INSTANT', 'NORMAL'])->default('NORMAL');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->enum('gender', [
                    'MALE',
                    'FEMALE',
                    'OTHER'
                ]);
            $table->text('jwt_token')->nullable();
            $table->string('country_code')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('picture')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('device_type',array('ANDROID','IOS'))->nullable();
            $table->enum('login_by',array('MANUAL','FACEBOOK','GOOGLE'));
            $table->string('social_unique_id')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude',15,8)->nullable();
            $table->string('stripe_cust_id')->nullable();
            $table->float('wallet_balance',15,2)->default(0);
            $table->float('rating', 4, 2)->default(5);
            $table->mediumInteger('otp')->default(0);
            $table->string('language')->default('en');
            $table->string('qrcode_url')->nullable();
            $table->string('referral_unique_id',10)->nullable();
            $table->mediumInteger('referal_count')->default(0);
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->tinyInteger('status')->default('1');
            $table->rememberToken();            
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
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
        Schema::dropIfExists('users');
    }
}
