<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToServicerequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('service')->table('service_requests', function (Blueprint $table) {
            $table->unsignedInteger('service_category_id')->after('provider_id');
            $table->foreign('service_category_id')->references('id')->on('service_categories')
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
        Schema::connection('service')->table('service_requests', function (Blueprint $table) {
             $table->unsignedInteger('service_category_id')->after('provider_id');
             $table->foreign('service_category_id')->references('id')->on('service_categories')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
