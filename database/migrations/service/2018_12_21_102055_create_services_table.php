<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('service')->create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_category_id');
            $table->unsignedInteger('service_subcategory_id');
            $table->unsignedInteger('company_id');
            $table->string('service_name');
            $table->string('picture')->nullable();
            $table->tinyInteger('allow_desc')->default(0)->comment = '1-Allow,0-Not Allow';
            $table->tinyInteger('allow_before_image')->default(0)->comment = '1-Allow,0-Not Allow';
            $table->tinyInteger('allow_after_image')->default(0)->comment = '1-Allow,0-Not Allow';
            $table->tinyInteger('is_professional')->default(0);
            $table->tinyInteger('service_status')->default(1);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_category_id')->references('id')->on('service_categories')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('service_subcategory_id')->references('id')->on('service_subcategories')
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
        Schema::dropIfExists('services');
    }
}
