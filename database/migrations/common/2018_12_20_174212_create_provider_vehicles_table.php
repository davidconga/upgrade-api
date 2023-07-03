<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id');            
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('vehicle_service_id')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->unsignedInteger('vehicle_year')->nullable();
            $table->string('vechile_image')->nullable();
            $table->string('picture')->nullable();
            $table->string('picture1')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->unsignedInteger('wheel_chair')->default(0);
            $table->unsignedInteger('child_seat')->default(0);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();

            // $table->foreign('provider_id')->references('id')->on('providers')
            //     ->onUpdate('cascade')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_vehicles');
    }
}
