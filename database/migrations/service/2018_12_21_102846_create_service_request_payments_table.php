<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRequestPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('service')->create('service_request_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_request_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('provider_id');
            $table->unsignedInteger('fleet_id');
            $table->unsignedInteger('promocode_id');
            $table->string('payment_id')->nullable();
            $table->unsignedInteger('company_id');
            $table->string('payment_mode')->nullable();
            $table->float('fixed', 10, 2)->default(0);
            $table->float('distance', 10, 2)->default(0);
            $table->float('minute', 10, 2)->default(0);
            $table->float('hour', 10, 2)->default(0);
            $table->float('commision', 10, 2)->default(0);
            $table->float('commision_percent', 10, 2)->default(0);
            $table->float('fleet', 10, 2)->default(0);
            $table->float('fleet_percent', 10, 2)->default(0);
            $table->float('discount', 10, 2)->default(0);
            $table->float('discount_percent', 10, 2)->default(0);
            $table->float('tax', 10, 2)->default(0);
            $table->float('tax_percent', 10, 2)->default(0);
            $table->float('wallet', 10, 2)->default(0);
            $table->float('extra_charges', 10, 2)->default(0);
            $table->string('extra_charges_notes')->nullable();
            $table->tinyInteger('is_partial')->nullable();
            $table->float('cash', 10, 2)->default(0);
            $table->float('card', 10, 2)->default(0);
            $table->float('surge', 10, 2)->default(0);
            $table->float('tips', 10, 2)->default(0);
            $table->float('total', 10, 2)->default(0);
            $table->float('round_of',  10, 2)->default(0);
            $table->float('payable', 10, 2)->default(0);
            $table->float('provider_pay', 10, 2)->default(0);
            $table->enum('created_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->enum('modified_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->enum('deleted_type', ['ADMIN','USER','PROVIDER','SHOP'])->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_request_id')->references('id')->on('service_requests')
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
        Schema::dropIfExists('service_request_payments');
    }
}
