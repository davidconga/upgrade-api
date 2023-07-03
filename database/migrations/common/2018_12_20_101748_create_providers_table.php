<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('providers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('first_name');
			$table->string('last_name');
			$table->string('payment_mode')->default('CASH');
			$table->string('email');
			$table->string('country_code')->nullable();
            $table->string('currency')->nullable();
            $table->string('currency_symbol')->nullable();
			$table->string('mobile');
			$table->string('password');
			$table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->nullable();
            $table->text('jwt_token')->nullable();
			$table->string('device_token')->nullable();
			$table->string('device_id')->nullable();
			$table->enum('device_type',array('ANDROID','IOS'))->nullable();
			$table->enum('login_by',array('MANUAL','FACEBOOK','GOOGLE'));
			$table->string('social_unique_id')->nullable();
			$table->double('latitude', 15, 8)->nullable();
			$table->double('longitude', 15, 8)->nullable();
			$table->text('current_location')->nullable();
			$table->string('stripe_cust_id')->nullable();
			$table->double('wallet_balance', 15, 2)->default(0);
			$table->tinyInteger('is_online')->default(0);
			$table->tinyInteger('is_assigned')->default(0);
			$table->float('rating', 4, 2)->default(5);
			$table->enum('status', ['DOCUMENT','CARD','ONBOARDING', 'APPROVED', 'BANNED'])->default('DOCUMENT'); 
			$table->tinyInteger('is_service')->default(0);
			$table->tinyInteger('is_document')->default(0);
			$table->tinyInteger('is_bankdetail')->default(0);
			$table->unsignedInteger('admin_id')->nullable();
			$table->string('payment_gateway_id')->nullable();
			$table->string('otp')->nullable();
			$table->string('language')->default('en');
			$table->string('picture')->nullable();
            $table->string('qrcode_url')->nullable();
            $table->string('referral_unique_id',10)->nullable();
            $table->mediumInteger('referal_count')->default(0);
			$table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
			$table->unsignedInteger('city_id')->nullable();
			$table->unsignedInteger('zone_id')->nullable();
			$table->timestamp('email_verified_at')->nullable();
			$table->tinyInteger('activation_status')->default('1');
			$table->rememberToken();
			$table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
			$table->unsignedInteger('company_id');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('admin_id')->references('id')->on('admins')
                ->onUpdate('cascade')->onDelete('set null');

			$table->foreign('country_id')->references('id')->on('countries')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('city_id')->references('id')->on('cities')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('zone_id')->references('id')->on('zones')
                ->onUpdate('cascade')->onDelete('set null');    

 
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('providers');
	}
}
