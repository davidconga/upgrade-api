<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Actions;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;
use App\Models\Common\CompanyCountry;
use App\Models\Common\CompanyCity;
use App\Models\Common\CountryBankForm;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\City;

use Auth;

class CompanyCountriesController extends Controller
{
    use Actions;
    private $model;
    private $request;
    public function __construct(CompanyCountry $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {   
        $datum = CompanyCountry::with('country')->where('company_id', Auth::user()->company_id);

        if($request->has('search_text') && $request->search_text != null) {

            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);

    }

    public function store(Request $request)
    {
        $this->validate($request, [
             'country_id' => 'required',
             'currency' => 'required',
             'currency_code' => 'required',
        ]);
       
        try{
            $company_country = new CompanyCountry;
            $company_country->company_id = Auth::user()->company_id;  
            $company_country->country_id = $request->country_id; 
            $company_country->currency = $request->currency;
            $company_country->currency_code = $request->currency_code;                          
            $company_country->status = '1';//$request->status;      
            $company_country->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $company_country = CompanyCountry::findOrFail($id);
            return Helper::getResponse(['data' => $company_country]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // 'country_id' => 'required',
            'currency' => 'required',
            'currency_code' => 'required',
            // 'status' => 'required',
        ]);
    
        try {
            $company_country = CompanyCountry::findOrFail($id);
            // $company_country->country_id = $request->country_id;
            $company_country->currency = $request->currency;
            $company_country->currency_code = $request->currency_code; 
            $company_country->update();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

    public function destroy($id)
    {
        return $this->removeModel($id);
    }
    public function countries(Request $request)
    {
        if(!empty($request->id)){
            
            $company_countries = CompanyCountry::where('company_id',\Auth::user()->company_id)                  ->where('id','!=', $request->id)
                                  ->pluck('country_id')->all();
         }else{

            $company_countries = CompanyCountry::where('company_id',\Auth::user()->company_id)
                             ->pluck('country_id')->all();
        }
        $country = Country::whereNotIn('id',$company_countries)->get();
        
        return Helper::getResponse(['data' => $country]);
    }
    public function states($country_id)
    {
        $states = State::where("country_id",$country_id)->get();
        return Helper::getResponse(['data' => $states]);
    }
    public function cities(Request $request, $state_id)
    {
        if(!empty($request->id)){
            
            $company_cities = CompanyCity::where('company_id',\Auth::user()->company_id)                  ->where('id','!=', $request->id)
                                  ->pluck('city_id')->all();
         }else{

            $company_cities = CompanyCity::where('company_id',\Auth::user()->company_id)
                             ->pluck('city_id')->all();
        }

        $cities = City::where("state_id",$state_id)->whereNotIn('id',$company_cities)->get();
        return Helper::getResponse(['data' => $cities]);
    }
    public function companyCountries()
    {
        $country = CompanyCountry::with('country')->get();
        return Helper::getResponse(['data' => $country]);
    }

    public function getBankForm($id)
    {
        try {
            $bank_forms = CountryBankForm::where('country_id',$id)->get();
            return Helper::getResponse(['data' => $bank_forms]);
        } catch (\Throwable $e) {
            \Log::info($e);
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function storeBankform(Request $request)
    {
        $id = $request->id;
        $company_country = CountryBankForm::where('country_id', $id)->get();
        if(!empty($company_country)){
            CountryBankForm::where('country_id', $id)->delete();
        }
        $count = count($request->types);

        try{
            for ($i=0; $i < $count ; $i++) { 
                \Log::info($request->label_ar[$i]);
                $bank_form = new CountryBankForm;
                $bank_form->country_id = $id;  
                $bank_form->type = $request->types[$i]; 
                $bank_form->label = $request->label_en[$i];
                 $bank_form->label_en = $request->label_en[$i];
                $bank_form->label_ar = $request->label_ar[$i];
                $bank_form->label_pt = $request->label_pt[$i];
                $bank_form->min = $request->mins[$i];                      
                $bank_form->max = $request->maxs[$i]; 
                $bank_form->company_id =Auth::user()->company_id; 
                $bank_form->save();
            }
            $company_country = CompanyCountry::where('country_id', $id)->first();
            $company_country->status = 1;
            $company_country->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            \Log::info($e);
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = CompanyCountry::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->status = 0;
                }else{
                    $datum->status = 1;
                }
            }
            $datum->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
