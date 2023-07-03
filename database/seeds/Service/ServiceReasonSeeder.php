<?php

use Illuminate\Database\Seeder;

class ServiceReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = null)
    {
        Schema::disableForeignKeyConstraints();

        DB::table('reasons')->insert([
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'USER', 'reason' => 'Provider does not reach location', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'PROVIDER', 'reason' => 'User mentioned different service to do', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'PROVIDER', 'reason' => 'User request to cancel', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'PROVIDER', 'reason' => 'User does not pay exact amount', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'USER', 'reason' => 'Provider requested to cancel', 'status' => 'Active'],
            ['company_id' => $company, 'service' => 'SERVICE', 'type' =>'USER', 'reason' => 'Provider delayed to reach location', 'status' => 'Active']
        ]);
        
        Schema::enableForeignKeyConstraints();
    }
}
