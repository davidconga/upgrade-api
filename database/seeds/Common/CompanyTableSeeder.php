<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Schema::disableForeignKeyConstraints();
    	
    	DB::table('companies')->truncate();
        DB::table('companies')->insert([
    		[
	            'company_name' => 'GoX',
                'domain' => '127.0.0.1',
                'base_url' => 'http://127.0.0.1:8001/api/v1',
                'socket_url' => 'http://127.0.0.1:8990',
                'access_key' => '123456',
                'expiry_date' => Carbon::now()->addYear(),
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
	        ]
	    ]);

	    Schema::enableForeignKeyConstraints();
    }
}
