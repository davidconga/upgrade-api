<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAddressesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_addresses', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('company_id');
			$table->enum('address_type', ['Home','Work','Other'])->nullable();;
			$table->string('landmark')->nullable();
			$table->string('flat_no')->nullable();
			$table->string('street')->nullable();
			$table->string('city')->nullable();
			$table->string('state')->nullable();
			$table->string('county')->nullable();
			$table->string('title')->nullable();
			$table->string('zipcode')->nullable();
			$table->double('latitude', 15, 8)->nullable();
			$table->double('longitude', 15, 8)->nullable();
			$table->text('map_address')->nullable();
			$table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users')
				->onUpdate('cascade')->onDelete('cascade');

			$table->foreign('created_by')->references('id')->on('users')
				->onUpdate('cascade')->onDelete('cascade');

			$table->foreign('modified_by')->references('id')->on('users')
				->onUpdate('cascade')->onDelete('cascade');

			$table->foreign('deleted_by')->references('id')->on('users')
				->onUpdate('cascade')->onDelete('cascade');
		});
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_addresses');
	}
}
