<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\Provider;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\ProviderService;
use App\Models\Common\User;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\City;
use App\Models\Common\Menu;
use App\Models\Common\MenuCity;
use App\Models\Transport\RideDeliveryVehicle;
use App\Models\Transport\RideType;
use App\Traits\Encryptable;
use Carbon\Carbon;

class ProviderVehicleSeeder extends Seeder
{
    use Encryptable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = null)
    {
    	Schema::disableForeignKeyConstraints();

        

        $providers = Provider::where('company_id', $company)->get();

        foreach ($providers as $provider) {

            DB::table('provider_vehicles')->insert([
                [
                    //'vehicle_service_id' => $provider_vehicle->id,
                    'provider_id' => $provider->id,
                    'company_id' => $company,
                    'vehicle_model' => 'BMW X6',
                    'vehicle_no' => '3D0979',
                    'vehicle_year' => '2019',
                    'vehicle_color' => 'Black',
                    'vehicle_make' => 'BMW',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);
        }

	    Schema::enableForeignKeyConstraints();
    }
}
