<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\City;
use App\Models\Common\State;
use App\Models\Common\Country;
use App\Models\Common\CompanyCountry;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCityAdminService;
use App\Models\Common\AdminService;
use Carbon\Carbon;

class CompanyCityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($company = null)
    {
        Schema::disableForeignKeyConstraints();

        $countries = Country::whereIn('country_code', ['US','MY','NG', 'IN', 'SY', 'AE', 'ID', 'MX', 'GB', 'JP','SG','AU','CA','ZA','KE','IR','AF','CN','PK','BD','FR','SD','JO','RU'])->get();

        $country_data = [];
        $country_id = [];

        foreach ($countries as $country) {
            $country_id[] = $country->id;
            $country_data[] = [
                'company_id' => $company,
                'country_id' => $country->id,
                'currency' => $country->country_symbol,
                'currency_code' => $country->country_currency,
                'status' => '1'
            ];
        }

        if(count($country_data) > 0) {
            foreach (array_chunk($country_data,1000) as $country_datum) {
                DB::table('company_countries')->insert($country_datum);
            }
        }


        

        $united_states = Country::where('country_code', 'US')->first()->id;
        $malaysia = Country::where('country_code', 'MY')->first()->id;
        $nigeria = Country::where('country_code', 'NG')->first()->id;
        $india = Country::where('country_code', 'IN')->first()->id;
        $syria = Country::where('country_code', 'SY')->first()->id;
        $united_arab_emirates = Country::where('country_code', 'AE')->first()->id;
        $indonesia = Country::where('country_code', 'ID')->first()->id;
        $mexico = Country::where('country_code', 'MX')->first()->id;
        $united_kingdom = Country::where('country_code', 'GB')->first()->id;
        $japan = Country::where('country_code',  'JP')->first()->id;
        $singapore = Country::where('country_code', 'SG')->first()->id;
        $australia = Country::where('country_code', 'AU')->first()->id;
        $canada = Country::where('country_code', 'CA')->first()->id;
        $south_africa = Country::where('country_code', 'ZA')->first()->id;
        $kenya = Country::where('country_code', 'KE')->first()->id;
        $iran = Country::where('country_code', 'IR')->first()->id;
        $afghanistan = Country::where('country_code', 'AF')->first()->id;
        $china = Country::where('country_code', 'CN')->first()->id;
        $pakistan = Country::where('country_code', 'PK')->first()->id;
        $bangladesh = Country::where('country_code', 'BD')->first()->id;
        $france = Country::where('country_code', 'FR')->first()->id;
        $sudan = Country::where('country_code', 'SD')->first()->id;
        $jordan = Country::where('country_code', 'JO')->first()->id;
        $russia = Country::where('country_code', 'RU')->first()->id;

        $washington_state = State::where('state_name', 'Washington')->where('country_id', $united_states)->first()->id;
        $New_York_state = State::where('state_name', 'New York')->where('country_id', $united_states)->first()->id;
        $melaka_state = State::where('state_name', 'Melaka')->where('country_id', $malaysia)->first()->id;
        $adamawa_state = State::where('state_name', 'Adamawa')->where('country_id', $nigeria)->first()->id;
        $tamil_nadu_state = State::where('state_name', 'Tamil Nadu')->where('country_id', $india)->first()->id;
        $dayr_az_zawr = State::where("state_name", "Dayr-az-Zawr")->where('country_id', $syria)->first()->id;
        $ajman_state = State::where('state_name', 'Ajman')->where('country_id', $united_arab_emirates)->first()->id;
        $Jakarta_state = State::where('state_name', 'Jakarta')->where('country_id', $indonesia)->first()->id;
        $mexico_state = State::where('state_name', 'Mexico')->where('country_id', $mexico)->first()->id;
        $llanymynech_state = State::where('state_name', 'Llanymynech')->where('country_id', $united_kingdom)->first()->id;
        $Tokyo_state = State::where('state_name',  'Tokyo')->where('country_id', $japan)->first()->id;
        $Singapore_state = State::where('state_name', 'Singapore')->where('country_id', $singapore)->first()->id;
        $Melbourne_state = State::where('state_name', 'Melbourne')->where('country_id', $australia)->first()->id;
        $ontario_state = State::where('state_name', 'Ontario')->where('country_id', $canada)->first()->id;
        $free_state = State::where("state_name", 'Free State')->where('country_id', $south_africa)->first()->id;
        $Nairobi_state = State::where('state_name', 'Nairobi')->where('country_id', $kenya)->first()->id;
        $Tehran_state = State::where('state_name', 'Tehran')->where('country_id', $iran)->first()->id;
        $Kabul_state = State::where('state_name', 'Kabul')->where('country_id', $afghanistan)->first()->id;
        $Beijing_state = State::where('state_name', 'Beijing')->where('country_id', $china)->first()->id;
        $balochistan_state = State::where('state_name', "Balochistan")->where('country_id', $pakistan)->first()->id;
        $Dhaka_state = State::where('state_name', 'Dhaka')->where('country_id', $bangladesh)->first()->id;
        $Paris_state = State::where('state_name', 'Paris')->where('country_id', $france)->first()->id;
        $junqali_state = State::where('state_name', 'Junqali')->where('country_id', $sudan)->first()->id;
        $Amman_state = State::where('state_name', 'Amman')->where('country_id', $jordan)->first()->id;
        $moskva_state = State::where('state_name', 'Moskva')->where('country_id', $russia)->first()->id;

        $alor_gajah = City::where('city_name', 'Alor Gajah')->where('state_id', $melaka_state)->first()->id;
        $Chennai = City::where('city_name', 'Chennai')->where('state_id', $tamil_nadu_state)->first()->id;
        $Charleston = City::where('city_name', 'Charleston')->where('state_id', $washington_state)->first()->id;
        $Demsa = City::where('city_name', 'Demsa')->where('state_id', $adamawa_state)->first()->id;
        $Damascus = City::where('city_name', 'Damascus')->where('state_id', $dayr_az_zawr)->first()->id;
        $Dubai = City::where('city_name', 'Dubai')->where('state_id', $ajman_state)->first()->id;
        $Jakarta = City::where('city_name', 'Jakarta')->where('state_id', $Jakarta_state)->first()->id;
        $Acahualco = City::where('city_name', 'Acahualco')->where('state_id', $mexico_state)->first()->id;
        $London = City::where('city_name', 'London')->where('state_id', $llanymynech_state)->first()->id;
        $Tokyo = City::where('city_name', 'Tokyo')->where('state_id', $Tokyo_state)->first()->id;
        $Poltar = City::where('city_name', 'Poltar')->where('state_id', $Singapore_state)->first()->id;
        $Melbourne = City::where('city_name', 'Melbourne')->where('state_id', $Melbourne_state)->first()->id;
        $Ottawa = City::where('city_name', 'Ottawa')->where('state_id', $ontario_state)->first()->id;
        $Pretoria = City::where('city_name', 'Pretoria')->where('state_id', $free_state)->first()->id;
        $Nairobi = City::where('city_name', 'Nairobi')->where('state_id', $Nairobi_state)->first()->id;
        $Tehran = City::where('city_name', 'Tehran')->where('state_id', $Tehran_state)->first()->id;
        $Kabul = City::where('city_name', 'Kabul')->where('state_id', $Kabul_state)->first()->id;
        $Beijing = City::where('city_name', 'Beijing')->where('state_id', $Beijing_state)->first()->id;
        $Barkhan = City::where('city_name', 'Barkhan')->where('state_id', $balochistan_state)->first()->id;
        $Dhaka = City::where('city_name', 'Dhaka')->where('state_id', $Dhaka_state)->first()->id;
        $Paris = City::where('city_name', 'Paris')->where('state_id', $Paris_state)->first()->id;
        $Kassala = City::where('city_name', 'Kassala')->where('state_id', $junqali_state)->first()->id;
        $Amman = City::where('city_name', 'Amman')->where('state_id', $Amman_state)->first()->id;
        $Nikel = City::where('city_name', 'Nikel')->where('state_id', $moskva_state)->first()->id;

        $cities = City::whereIn('id', [$alor_gajah, $Chennai, $Charleston, $Demsa, $Damascus, $Dubai, $Jakarta, $Acahualco, $London, $Tokyo, $Poltar, $Melbourne, $Ottawa, $Pretoria, $Nairobi, $Tehran, $Kabul, $Beijing, $Dhaka, $Paris, $Kassala, $Amman, $Nikel])->get();

        $city_data = [];

        foreach ($cities as $city) {
            $city_data[] = [
                'company_id' => $company,
                'country_id' => $city->country_id,
                'state_id' => $city->state_id,
                'city_id' => $city->id,
                'status' => '1'
            ];
        }

        if(count($city_data) > 0) {
            foreach (array_chunk($city_data,1000) as $city_datum) {
                DB::table('company_cities')->insert($city_datum);
            }
        }


        
        $bank_countries_list = [$united_states, $malaysia, $nigeria, $india, $syria, $united_arab_emirates, $indonesia, $mexico, $united_kingdom, $japan, $singapore, $australia, $canada, $south_africa, $kenya, $iran, $afghanistan, $china, $pakistan, $bangladesh, $france, $sudan, $jordan, $russia];

        $bank_countries = [];

        foreach ($bank_countries_list as $bank_country) {
           $bank_countries[] = 
                [
                    'company_id' => $company,
                    'country_id' => $bank_country,
                    'type' => 'VARCHAR',
                    'label' => 'Bank Name',
                    'min' => 1,
                    'max' => 255
                ];
            $bank_countries[] = 
                [
                    'company_id' => $company,
                    'country_id' => $bank_country,
                    'type' => 'VARCHAR',
                    'label' => 'IFSC Code',
                    'min' => 1,
                    'max' => 255
                ];
            $bank_countries[] = 
                [
                    'company_id' => $company,
                    'country_id' => $bank_country,
                    'type' => 'VARCHAR',
                    'label' => 'Account Holder Name',
                    'min' => 1,
                    'max' => 255
                ];
            $bank_countries[] = 
                [
                    'company_id' => $company,
                    'country_id' => $bank_country,
                    'type' => 'VARCHAR',
                    'label' => 'Account Number',
                    'min' => 1,
                    'max' => 255
                ];
        }

        


        DB::table('country_bank_forms')->insert($bank_countries);

        

        $admin_services = AdminService::where('company_id', $company)->get();
        $company_cities = CompanyCity::where('company_id', $company)->get();

        $company_city_data = [];

        foreach ($admin_services as $admin_service) {
            foreach ($company_cities as $company_city) {
                $company_city_data[] = [
                    'admin_service' => $admin_service->admin_service,
                    'company_city_service_id' => $company_city->id
                ];
            }
        }
        

        if(count($company_city_data) > 0) {
            foreach (array_chunk($company_city_data,1000) as $company_city_datum) {
                DB::table('company_city_admin_services')->insert($company_city_datum);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
