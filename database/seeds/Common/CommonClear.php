<?php

use App\Models\Common\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\Encryptable;
use Carbon\Carbon;

class CommonClear extends Seeder
{

    use Encryptable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Schema::disableForeignKeyConstraints();

    	DB::table('menus')->truncate();

        DB::table('menu_cities')->truncate();

        DB::table('provider_services')->truncate();

        DB::table('documents')->truncate();

        DB::table('disputes')->truncate();

        DB::table('disputes')->truncate();

        DB::table('reasons')->truncate();

        DB::table('promocodes')->truncate();

	    Schema::enableForeignKeyConstraints();
    }
}
