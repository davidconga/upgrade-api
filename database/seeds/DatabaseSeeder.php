<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->call('CompanyTableSeeder');
        $this->call('AdminServiceTableSeeder');
        $this->call('RoleTableSeeder');
        $this->call('PermissionTableSeeder');

        $this->call('CountriesTableSeeder');
        $this->call('StatesTableSeeder');
        $this->call('CitiesTableSeeder');

        //$this->call('SettingsTableSeeder');

        //$this->call('AdminTableSeeder');
        //$this->call('CompanyCityTableSeeder');
        //$this->call('DemoTableSeeder');
        //$this->call('ProviderVehicleSeeder');

        //$this->call('CmsPageSeeder');


        //$this->call('CommonClear');

        //$this->call('DocumentSeeder');

        //TRANSPORT
        /*$this->call('TransportTableSeeder');
        $this->call('TransportDisputeSeeder');
        $this->call('TransportDocumentSeeder');
        $this->call('TransportReasonSeeder');
        $this->call('TransportPromocodeSeeder');*/


        //SERVICE
        /*$this->call('ServiceCategoryTableSeeder');
        $this->call('ServiceSubCategoriesSeeder');
        $this->call('ServiceDisputeSeeder');
        $this->call('ServiceReasonSeeder');
        $this->call('ServiceTableSeeder');
        $this->call('ServiceSeeder');
        $this->call('ServicePromocodeSeeder');*/


        //ORDER
        /*$this->call('OrderDisputeSeeder');
        $this->call('OrderReasonSeeder');
        $this->call('StoreTypeSeeder');
        $this->call('CuisineTableSeeder');
        $this->call('StoreTableSeeder');
        $this->call('OrderPromocodeSeeder');

        $this->call('StoreAddonTableSeeder');
        $this->call('StoreCuisineTableSeeder');
        $this->call('StoreTimingTableSeeder');
        $this->call('StoreCategoryTableSeeder');

        $this->call('StoreItemTableSeeder');
        $this->call('StoreItemAddonTableSeeder');
        $this->call('OrderTableSeeder');*/





    }
}
