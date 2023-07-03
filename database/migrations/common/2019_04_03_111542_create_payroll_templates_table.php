<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('common')->create('payroll_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('template_name');
            $table->integer('company_id');
            $table->integer('zone_id');
            $table->enum('status', [
                    'ACTIVE',
                    'INACTIVE'
                ])->default('ACTIVE');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_templates');
    }
}
