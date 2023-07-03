<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLangToReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('common')->table('reasons', function (Blueprint $table) {
            $table->string('lang')->nullable()->after('reason');
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
