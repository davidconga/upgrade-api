<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\CompanyCity;
use App\Models\Common\Provider;
use App\Models\Common\Menu;
use Carbon\Carbon;

class ServiceCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = null)
    {
    	Schema::connection('service')->disableForeignKeyConstraints();

        $service = DB::table('admin_services')->where('admin_service', 'SERVICE')->first();

        $service_categories = [
            ['company_id' => $company, 'service_category_name' => 'Electrician', 'alias_name' => 'Electrician', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Plumber', 'alias_name' => 'Plumber', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Tutors', 'alias_name' => 'Tutors', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Carpenter', 'alias_name' => 'Carpenter', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Mechanic', 'alias_name' => 'Mechanic', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Beautician', 'alias_name' => 'Beautician', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'DJ', 'alias_name' => 'DJ', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Massage', 'alias_name' => 'Massage', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Tow Truck', 'alias_name' => 'Tow Truck', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Painting', 'alias_name' => 'Painting', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Car Wash', 'alias_name' => 'Car Wash', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'PhotoGraphy', 'alias_name' => 'PhotoGraphy', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Doctors', 'alias_name' => 'Doctors', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Dog Walking', 'alias_name' => 'Dog Walking', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Baby Sitting', 'alias_name' => 'Baby Sitting', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Fitness Coach', 'alias_name' => 'Fitness Coach', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Maids', 'alias_name' => 'Maids', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Pest Control', 'alias_name' => 'Pest Control', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Home Painting', 'alias_name' => 'Home Painting', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'PhysioTheraphy', 'alias_name' => 'PhysioTheraphy', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Catering', 'alias_name' => 'Catering', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Dog Grooming', 'alias_name' => 'Dog Grooming', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Vet', 'alias_name' => 'Vet', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Snow Plows', 'alias_name' => 'Snow Plows', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Workers', 'alias_name' => 'Workers', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Lock Smith', 'alias_name' => 'Lock Smith', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Travel Agent', 'alias_name' => 'Travel Agent', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Tour Guide', 'alias_name' => 'Tour Guide', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Insurance Agent', 'alias_name' => 'Insurance Agent', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Security Guard', 'alias_name' => 'Security Guard', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Fuel', 'alias_name' => 'Fuel', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Lawn Mowing', 'alias_name' => 'Law Mowing', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Barber', 'alias_name' => 'Barber', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Interior Decorator', 'alias_name' => 'Interior Decorator', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Lawn Care', 'alias_name' => 'Lawn Care', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Carpet Repairer', 'alias_name' => 'Carpet Repairer', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Computer Repairer', 'alias_name' => 'Computer Repairer', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Cuddling', 'alias_name' => 'Cuddling', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Fire Fighters', 'alias_name' => 'Fire Fighters', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Helpers', 'alias_name' => 'Helpers', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Lawyers', 'alias_name' => 'Lawyers', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Mobile Technician', 'alias_name' => 'Mobile Technician', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Office Cleaning', 'alias_name' => 'Office Cleaning', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Party Cleaning', 'alias_name' => 'Party Cleaning', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Psychologist', 'alias_name' => 'Psychologist', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Road Assistance', 'alias_name' => 'Road Assistance', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Sofa Repairer', 'alias_name' => 'Sofa Repairer', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Spa', 'alias_name' => 'Spa', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1],
            ['company_id' => $company, 'service_category_name' => 'Translator', 'alias_name' => 'Translator', 'price_choose' => 'admin_price', 'picture' => '', 'service_category_order' => 0, 'service_category_status' => 1]
        ];

        foreach (array_chunk($service_categories,1000) as $service_category) {
            DB::connection('service')->table('service_categories')->insert($service_category);
        }


	    Schema::connection('service')->enableForeignKeyConstraints();
    }
}
