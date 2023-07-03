<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('transaction_id')->nullable();
            $table->integer('template_id')->nullable();
            $table->integer('provider_id')->nullable();
            $table->enum('admin_service', ['TRANSPORT','ORDER','SERVICE'])->nullable(); 
            $table->string('wallet')->nullable();
            $table->enum('type', [
                    'PROVIDER',
                    'FLEET',
                    'STORE'
                ]);
            $table->enum('payroll_type', [
                    'MANUAL',
                    'TEMPLATE'
                ])->default('MANUAL');
            $table->enum('status', [
                    'PENDING',
                    'CANCEL',
                    'COMPLETED'
                ])->default('PENDING');
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
        Schema::dropIfExists('payrolls');
    }
}
