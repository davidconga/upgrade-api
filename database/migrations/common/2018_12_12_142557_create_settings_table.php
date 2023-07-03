<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateSettingsTable extends Migration
{
    public function __construct()
    {
        $this->tablename = Config::get('settings.table');
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->text('settings_data');
            $table->integer('demo_mode')->default(0);
            $table->integer('error_mode')->default(0);
            $table->integer('encrypt')->default(0);
            $table->integer('banner')->default(0);
            $table->integer('chat')->default(0);
            $table->integer('clear_seed')->default(0);
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
        Schema::dropIfExists('settings');
    }
}
