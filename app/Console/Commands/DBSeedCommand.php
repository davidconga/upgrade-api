<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;
use App\Models\Common\Company;
use Carbon\Carbon;
use DB;

class DBSeedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'company:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seed data to company';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		if ($this->confirm('Do you wish to clear existing data?')) {

			Schema::disableForeignKeyConstraints();

			DB::table('admin_services')->truncate();
			DB::table('settings')->truncate();
			DB::table('admins')->truncate();
			DB::table('company_countries')->truncate();
			DB::table('company_cities')->truncate();
			DB::table('country_bank_forms')->truncate();
			DB::table('company_city_admin_services')->truncate();
			DB::table('users')->truncate();
			DB::table('providers')->truncate();
			DB::table('provider_vehicles')->truncate();
			DB::table('cms_pages')->truncate();
			DB::table('menus')->truncate();
			DB::table('menu_cities')->truncate();
			DB::table('provider_services')->truncate();
			DB::table('documents')->truncate();
			DB::table('disputes')->truncate();
			DB::table('disputes')->truncate();
			DB::table('reasons')->truncate();
			DB::table('promocodes')->truncate();

			

			DB::connection('service')->table('service_cities')->delete();
			DB::connection('service')->statement('ALTER TABLE service_cities AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_city_prices')->truncate();
			DB::connection('service')->table('services')->delete();
			DB::connection('service')->statement('ALTER TABLE services AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_categories')->delete();
			DB::connection('service')->statement('ALTER TABLE service_categories AUTO_INCREMENT = 1;');
			DB::connection('service')->table('service_subcategories')->delete();
			DB::connection('service')->statement('ALTER TABLE service_subcategories AUTO_INCREMENT = 1;');

			;

			Schema::enableForeignKeyConstraints();

		}

		$company_name = $this->ask('Enter your company name');

		$existing_company = DB::table('companies')->where('company_name', $company_name)->first();

		if($existing_company != null) {
			$this->error('Company already exists!');
		} else {
			if ($this->confirm('Do you wish to continue?')) {
				$company = Company::create([
					'company_name' => $company_name,
					'domain' => '127.0.0.1',
					'base_url' => 'http://127.0.0.1:8001/api/v1',
					'socket_url' => 'http://127.0.0.1:8990',
					'access_key' => '123456',
					'expiry_date' => Carbon::now()->addYear()
				]);



				(new \AdminServiceTableSeeder())->run($company->id);
				(new \SettingsTableSeeder())->run($company->id);
				(new \AdminTableSeeder())->run($company->id);
				(new \CompanyCityTableSeeder())->run($company->id);
				(new \DemoTableSeeder())->run($company->id);
				(new \ProviderVehicleSeeder())->run($company->id);
				(new \CmsPageSeeder())->run($company->id);
				(new \DocumentSeeder())->run($company->id);

				

				(new \ServiceCategoryTableSeeder())->run($company->id);
				(new \ServiceSubCategoriesSeeder())->run($company->id);
				(new \ServiceDisputeSeeder())->run($company->id);
				(new \ServiceReasonSeeder())->run($company->id);
				(new \ServiceTableSeeder())->run($company->id);
				(new \ServiceSeeder())->run($company->id);
				(new \ServicePromocodeSeeder())->run($company->id);

				

				$this->info('Seed Data completed');
			}
		}
		
	}
}
