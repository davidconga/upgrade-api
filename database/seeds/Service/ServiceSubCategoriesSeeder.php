<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\CompanyCity;
use App\Models\Common\Provider;
use App\Models\Common\Menu;
use Carbon\Carbon;

class ServiceSubCategoriesSeeder extends Seeder
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

        $Electrician = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Electrician')->first()->id;
        $Plumber = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Plumber')->first()->id;
        $Tutors = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Tutors')->first()->id;
        $Carpenter = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Carpenter')->first()->id;
        $Mechanic = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Mechanic')->first()->id;
        $Beautician = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Beautician')->first()->id;
        $DJ = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'DJ')->first()->id;
        $Massage = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Massage')->first()->id;
        $Tow_Truck = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Tow Truck')->first()->id;
        $Painting = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Painting')->first()->id;
        $Car_Wash = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Car Wash')->first()->id;
        $PhotoGraphy = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'PhotoGraphy')->first()->id;
        $Doctors = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Doctors')->first()->id;
        $Dog_Walking = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Dog Walking')->first()->id;
        $Baby_Sitting = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Baby Sitting')->first()->id;
        $Fitness_Coach = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Fitness Coach')->first()->id;
        $Maids = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Maids')->first()->id;
        $Pest_Control = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Pest Control')->first()->id;
        $Home_Painting = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Home Painting')->first()->id;
        $PhysioTheraphy = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'PhysioTheraphy')->first()->id;
        $Catering = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Catering')->first()->id;
        $Dog_Gromming = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Dog Grooming')->first()->id;
        $Vet = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Vet')->first()->id;
        $Snow_Plows = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Snow Plows')->first()->id;
        $Workers = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Workers')->first()->id;
        $Lock_Smith = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Lock Smith')->first()->id;
        $Travel_Agent = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Travel Agent')->first()->id;
        $Tour_Guide = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Tour Guide')->first()->id;
        $Insurance_Agent = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Insurance Agent')->first()->id;
        $Security_Guard = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Security Guard')->first()->id;
        $Fuel = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Fuel')->first()->id;
        $Law_Mowing = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Lawn Mowing')->first()->id;
        $Barber = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Barber')->first()->id;
        $Interior_Decorator = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Interior Decorator')->first()->id;
        $Lawn_Care = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Lawn Care')->first()->id;
        $Carpet_Repairer = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Carpet Repairer')->first()->id;
        $Computer_Repairer = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Computer Repairer')->first()->id;
        $Cuddling = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Cuddling')->first()->id;
        $Fire_Fighters = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Fire Fighters')->first()->id;
        $Helpers = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Helpers')->first()->id;
        $Lawyers = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Lawyers')->first()->id;
        $Mobile_Technician = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Mobile Technician')->first()->id;
        $Office_Cleaning = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Office Cleaning')->first()->id;
        $Party_Cleaning = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Party Cleaning')->first()->id;
        $Psychologist = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Psychologist')->first()->id;
        $Road_Assistance = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Road Assistance')->first()->id;
        $Sofa_Repairer = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Sofa Repairer')->first()->id;
        $Spa = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Spa')->first()->id;
        $Translator = DB::connection('service')->table('service_categories')->where('company_id', $company)->where('service_category_name', 'Translator')->first()->id;


        $service_subcategories = [
            ['service_category_id' => $Electrician, 'company_id' => $company, 'service_subcategory_name' => 'Wiring', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Plumber, 'company_id' => $company, 'service_subcategory_name' => 'Blocks and Leakage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tutors, 'company_id' => $company, 'service_subcategory_name' => 'Maths', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tutors, 'company_id' => $company, 'service_subcategory_name' => 'Science', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpenter, 'company_id' => $company, 'service_subcategory_name' => 'Bolt Latch', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpenter, 'company_id' => $company, 'service_subcategory_name' => 'Furniture Installation', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpenter, 'company_id' => $company, 'service_subcategory_name' => 'Carpentry Work', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Mechanic, 'company_id' => $company, 'service_subcategory_name' => 'General Mechanic', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Mechanic, 'company_id' => $company, 'service_subcategory_name' => 'Car Mechanic', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Mechanic, 'company_id' => $company, 'service_subcategory_name' => 'Bike Mechanic', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Beautician, 'company_id' => $company, 'service_subcategory_name' => 'Hair Style', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Beautician, 'company_id' => $company, 'service_subcategory_name' => 'Makeup', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Beautician, 'company_id' => $company, 'service_subcategory_name' => 'BlowOut', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Beautician, 'company_id' => $company, 'service_subcategory_name' => 'Facial', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $DJ, 'company_id' => $company, 'service_subcategory_name' => 'Weddings', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $DJ, 'company_id' => $company, 'service_subcategory_name' => 'Parties', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Massage, 'company_id' => $company, 'service_subcategory_name' => 'Deep Tissue Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Massage, 'company_id' => $company, 'service_subcategory_name' => 'Thai Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Massage, 'company_id' => $company, 'service_subcategory_name' => 'Swedish Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tow_Truck, 'company_id' => $company, 'service_subcategory_name' => 'Flat Tier', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tow_Truck, 'company_id' => $company, 'service_subcategory_name' => 'Towing', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tow_Truck, 'company_id' => $company, 'service_subcategory_name' => 'Key Lock Out', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Painting, 'company_id' => $company, 'service_subcategory_name' => 'Interior Painting', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Painting, 'company_id' => $company, 'service_subcategory_name' => 'Exterior Painting', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $PhotoGraphy, 'company_id' => $company, 'service_subcategory_name' => 'Wedding', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Plumber, 'company_id' => $company, 'service_subcategory_name' => 'Tap and wash basin', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Electrician, 'company_id' => $company, 'service_subcategory_name' => 'Fans', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Electrician, 'company_id' => $company, 'service_subcategory_name' => 'Switches and Meters', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Electrician, 'company_id' => $company, 'service_subcategory_name' => 'Lights', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Electrician, 'company_id' => $company, 'service_subcategory_name' => 'Others', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Plumber, 'company_id' => $company, 'service_subcategory_name' => 'Toilet', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Plumber, 'company_id' => $company, 'service_subcategory_name' => 'Bathroom Fitting', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Plumber, 'company_id' => $company, 'service_subcategory_name' => 'Water Tank', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Dog_Walking, 'company_id' => $company, 'service_subcategory_name' => 'Walking', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Baby_Sitting, 'company_id' => $company, 'service_subcategory_name' => 'Day Care', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tutors, 'company_id' => $company, 'service_subcategory_name' => 'English', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tutors, 'company_id' => $company, 'service_subcategory_name' => 'Social Science', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tutors, 'company_id' => $company, 'service_subcategory_name' => 'Computer', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Car_Wash, 'company_id' => $company, 'service_subcategory_name' => 'Hatchback', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Car_Wash, 'company_id' => $company, 'service_subcategory_name' => 'Sedan', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Car_Wash, 'company_id' => $company, 'service_subcategory_name' => 'SUV', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $PhotoGraphy, 'company_id' => $company, 'service_subcategory_name' => 'Photoshoot', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Doctors, 'company_id' => $company, 'service_subcategory_name' => 'General Physician', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Doctors, 'company_id' => $company, 'service_subcategory_name' => 'Cardiologist', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Doctors, 'company_id' => $company, 'service_subcategory_name' => 'Dermatologist', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Baby_Sitting, 'company_id' => $company, 'service_subcategory_name' => 'After School Sitters', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Baby_Sitting, 'company_id' => $company, 'service_subcategory_name' => 'Date Night sitters', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Baby_Sitting, 'company_id' => $company, 'service_subcategory_name' => 'Tutoring and lessons', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fitness_Coach, 'company_id' => $company, 'service_subcategory_name' => 'Aerobic Exercise', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fitness_Coach, 'company_id' => $company, 'service_subcategory_name' => 'Resistance Exercise', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fitness_Coach, 'company_id' => $company, 'service_subcategory_name' => 'Flexibility Training', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Maids, 'company_id' => $company, 'service_subcategory_name' => 'Full Home Deep Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Maids, 'company_id' => $company, 'service_subcategory_name' => 'Party Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Maids, 'company_id' => $company, 'service_subcategory_name' => 'Office Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Maids, 'company_id' => $company, 'service_subcategory_name' => 'Water Tank Storage Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Pest_Control, 'company_id' => $company, 'service_subcategory_name' => 'Termite Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Pest_Control, 'company_id' => $company, 'service_subcategory_name' => 'Cockroach Treatment', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Pest_Control, 'company_id' => $company, 'service_subcategory_name' => 'Bed Bugs Treatment', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Pest_Control, 'company_id' => $company, 'service_subcategory_name' => 'Mosquito Treatment', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $PhysioTheraphy, 'company_id' => $company, 'service_subcategory_name' => 'Muscle And Joint Pain', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $PhysioTheraphy, 'company_id' => $company, 'service_subcategory_name' => 'Knee Pain', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $PhysioTheraphy, 'company_id' => $company, 'service_subcategory_name' => 'sciatica', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Catering, 'company_id' => $company, 'service_subcategory_name' => 'Lunch Meetings', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Catering, 'company_id' => $company, 'service_subcategory_name' => 'Wedding Catering', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Catering, 'company_id' => $company, 'service_subcategory_name' => 'Event Catering', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Catering, 'company_id' => $company, 'service_subcategory_name' => 'Office Catering', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Dog_Gromming, 'company_id' => $company, 'service_subcategory_name' => 'Clippers and Scissors', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Dog_Gromming, 'company_id' => $company, 'service_subcategory_name' => 'Nail Clippers', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Dog_Gromming, 'company_id' => $company, 'service_subcategory_name' => 'Brushes and Combs', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Vet, 'company_id' => $company, 'service_subcategory_name' => 'Surgery', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Vet, 'company_id' => $company, 'service_subcategory_name' => 'Vaccine', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Vet, 'company_id' => $company, 'service_subcategory_name' => 'Disease', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Snow_Plows, 'company_id' => $company, 'service_subcategory_name' => 'Plow Category 1', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Snow_Plows, 'company_id' => $company, 'service_subcategory_name' => 'Plow Category 2', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Workers, 'company_id' => $company, 'service_subcategory_name' => 'Home Work', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Workers, 'company_id' => $company, 'service_subcategory_name' => 'Office Work', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lock_Smith, 'company_id' => $company, 'service_subcategory_name' => 'Residential', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lock_Smith, 'company_id' => $company, 'service_subcategory_name' => 'Commercial', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lock_Smith, 'company_id' => $company, 'service_subcategory_name' => 'Automobile', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Travel_Agent, 'company_id' => $company, 'service_subcategory_name' => 'Ticket Booking', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Travel_Agent, 'company_id' => $company, 'service_subcategory_name' => 'Hotel Booking', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tour_Guide, 'company_id' => $company, 'service_subcategory_name' => 'Sight Seeing', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tour_Guide, 'company_id' => $company, 'service_subcategory_name' => 'Trekking', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Tour_Guide, 'company_id' => $company, 'service_subcategory_name' => 'Walking Tour', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Insurance_Agent, 'company_id' => $company, 'service_subcategory_name' => 'Health Insurance Agent', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Insurance_Agent, 'company_id' => $company, 'service_subcategory_name' => 'Mutual Fund Agent', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Security_Guard, 'company_id' => $company, 'service_subcategory_name' => 'Personal Security', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Security_Guard, 'company_id' => $company, 'service_subcategory_name' => 'Commercial Security', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Security_Guard, 'company_id' => $company, 'service_subcategory_name' => 'Residential Category', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fuel, 'company_id' => $company, 'service_subcategory_name' => 'Petrol', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fuel, 'company_id' => $company, 'service_subcategory_name' => 'Diesel', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fuel, 'company_id' => $company, 'service_subcategory_name' => 'LPG', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Law_Mowing, 'company_id' => $company, 'service_subcategory_name' => '0 to 2000 Sqft Lawn', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Law_Mowing, 'company_id' => $company, 'service_subcategory_name' => '2000 to 4000 Sqft Lawn', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Law_Mowing, 'company_id' => $company, 'service_subcategory_name' => '4000 to 6000 Sqft Lawn', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Barber, 'company_id' => $company, 'service_subcategory_name' => 'Hair Cutting', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Barber, 'company_id' => $company, 'service_subcategory_name' => 'Hair Dressing', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Barber, 'company_id' => $company, 'service_subcategory_name' => 'Shaving', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Interior_Decorator, 'company_id' => $company, 'service_subcategory_name' => 'Home Interior', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Interior_Decorator, 'company_id' => $company, 'service_subcategory_name' => 'Office Interior', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lawn_Care, 'company_id' => $company, 'service_subcategory_name' => 'Pest Control', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lawn_Care, 'company_id' => $company, 'service_subcategory_name' => 'Mosquito Treatment', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lawn_Care, 'company_id' => $company, 'service_subcategory_name' => 'Bed Bug Treatment', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpet_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Sofa Cleaning Services', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpet_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Carpet Shampooing Services', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Carpet_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Commercial Carpet Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Computer_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Laptop', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Computer_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Desktop', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Computer_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Mac', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Cuddling, 'company_id' => $company, 'service_subcategory_name' => 'Category 1', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Cuddling, 'company_id' => $company, 'service_subcategory_name' => 'Category 2', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fire_Fighters, 'company_id' => $company, 'service_subcategory_name' => 'Water Pumps and hoses', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Fire_Fighters, 'company_id' => $company, 'service_subcategory_name' => 'Fire Extinguishers', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Helpers, 'company_id' => $company, 'service_subcategory_name' => 'Category 1', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Helpers, 'company_id' => $company, 'service_subcategory_name' => 'Category 2', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lawyers, 'company_id' => $company, 'service_subcategory_name' => 'Civil Lawyers', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Lawyers, 'company_id' => $company, 'service_subcategory_name' => 'Lawyers for Property Case', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Mobile_Technician, 'company_id' => $company, 'service_subcategory_name' => 'Mobile', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Office_Cleaning, 'company_id' => $company, 'service_subcategory_name' => 'Vacuum All Floors', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Office_Cleaning, 'company_id' => $company, 'service_subcategory_name' => 'Clean and Replace bins', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Office_Cleaning, 'company_id' => $company, 'service_subcategory_name' => 'Lobby and Workplace', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Party_Cleaning, 'company_id' => $company, 'service_subcategory_name' => 'Dinning Washout', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Party_Cleaning, 'company_id' => $company, 'service_subcategory_name' => 'Table Cleaning', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Psychologist, 'company_id' => $company, 'service_subcategory_name' => 'Counselors', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Psychologist, 'company_id' => $company, 'service_subcategory_name' => 'Child Psychologist', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Psychologist, 'company_id' => $company, 'service_subcategory_name' => 'Cognitive Behavioral Therapists', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Road_Assistance, 'company_id' => $company, 'service_subcategory_name' => 'Vehicle Breakdown', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Road_Assistance, 'company_id' => $company, 'service_subcategory_name' => 'Towing', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Road_Assistance, 'company_id' => $company, 'service_subcategory_name' => 'Battery Service', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Sofa_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Furniture Repair', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Sofa_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Chair Repair', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Sofa_Repairer, 'company_id' => $company, 'service_subcategory_name' => 'Furniture Upholstery', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Spa, 'company_id' => $company, 'service_subcategory_name' => 'Aromatherapy Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Spa, 'company_id' => $company, 'service_subcategory_name' => 'Balinese Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Spa, 'company_id' => $company, 'service_subcategory_name' => 'Swedish Massage', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Translator, 'company_id' => $company, 'service_subcategory_name' => 'Category 1', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Translator, 'company_id' => $company, 'service_subcategory_name' => 'Category 2', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1],
['service_category_id' => $Mechanic, 'company_id' => $company, 'service_subcategory_name' => 'Fridge', 'picture' => '', 'service_subcategory_order' => 0, 'service_subcategory_status' => 1]
        ];

        foreach (array_chunk($service_subcategories,1000) as $service_subcategory) {
            DB::connection('service')->table('service_subcategories')->insert($service_subcategory);
        }

        

	    Schema::connection('service')->enableForeignKeyConstraints();
    }
}
