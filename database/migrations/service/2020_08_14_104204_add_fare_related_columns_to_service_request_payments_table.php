<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFareRelatedColumnsToServiceRequestPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('service')->table('service_request_payments', function (Blueprint $table) {
            $table->float('mins_fare', 10, 2)->default(0)->after('fixed');
            $table->float('distance_fare', 10, 2)->default(0)->after('fixed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('service')->table('service_request_payments', function (Blueprint $table) {
            $table->dropColumn('mins_fare');
            $table->dropColumn('distance_fare');
        });
    }
}
