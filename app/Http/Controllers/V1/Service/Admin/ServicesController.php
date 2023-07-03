<?php

namespace App\Http\Controllers\V1\Service\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Service\ServiceCategory;
use App\Models\Service\ServiceSubcategory;
use App\Models\Service\Service;
use App\Models\Service\ServiceRequest;
use App\Models\Service\ServiceCityPrice;
use App\Models\Common\AdminService;
use App\Models\Common\CompanyCountry;
use App\Models\Common\Menu;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Auth;
use Validator;

class ServicesController extends Controller
{
    use Actions;
    private $model;
    private $request;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Service $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datum = Service::with('serviceCategory')->with('subCategories')->where('company_id', Auth::user()->company_id);
        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }
        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }
        $data = $datum->paginate(10);
        
        return Helper::getResponse(['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'service_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',            
            // 'service_name' => 'required|max:255|regex:/^[a-zA-Z0-9\s]+$/',            
            'service_category_id'=>'required',
            'service_subcategory_id' => 'required',
            //'picture' => 'required|mimes:jpeg,jpg,bmp,png|max:5242880',
            'service_status' => 'required',
        ]);
        try {
            $SubCategory = new Service;
            $SubCategory->company_id = Auth::user()->company_id; 
            $SubCategory->service_name = $request->service_name; 
            $SubCategory->service_category_id = $request->service_category_id;            
            $SubCategory->service_subcategory_id = $request->service_subcategory_id;
            $SubCategory->service_status = $request->service_status;

            if(!empty($request->is_professional))
                $SubCategory->is_professional = $request->is_professional;
            else
                $SubCategory->is_professional=0;

            if(!empty($request->allow_desc))
                $SubCategory->allow_desc = $request->allow_desc;
            else
                $SubCategory->allow_desc=0;
                
            if(!empty($request->allow_before_image))
                $SubCategory->allow_before_image = $request->allow_before_image;
            else
                $SubCategory->allow_before_image=0;

            if(!empty($request->allow_after_image))
                $SubCategory->allow_after_image = $request->allow_after_image;
            else
                $SubCategory->allow_after_image=0;
            /*if($request->hasFile('picture')) {
                $SubCategory->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'service-'.time().'.png');
            }*/
            $SubCategory->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        }catch (\Throwable $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $ServiceView = Service::with('subcategories')->findOrFail($id);

            $ServiceView['service_subcategory_data']=ServiceSubcategory::where("service_category_id",$ServiceView->service_category_id)->get();

            return Helper::getResponse(['data' => $ServiceView]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'service_name' => 'required|max:255|regex:/^[a-zA-Z0-9\x{00C0}-\x{00ff}\s\/\-\)\(\`\.\"\']+$/u',            
            'service_category_id'=>'required',
            'service_subcategory_id' => 'required',
            //'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'service_status' => 'required',
        ]);
        try{
            $ServiceQuery = Service::findOrFail($id);
            if($ServiceQuery){
                $ServiceQuery->service_name = $request->service_name; 
                $ServiceQuery->service_category_id = $request->service_category_id;            
                $ServiceQuery->service_subcategory_id = $request->service_subcategory_id;
                $ServiceQuery->service_status = $request->service_status;
                if(!empty($request->is_professional))
                    $ServiceQuery->is_professional = $request->is_professional;
                else
                    $ServiceQuery->is_professional=0;

                if(!empty($request->allow_desc))
                    $ServiceQuery->allow_desc = $request->allow_desc;
                else
                    $ServiceQuery->allow_desc=0;
                    
                if(!empty($request->allow_before_image))
                    $ServiceQuery->allow_before_image = $request->allow_before_image;
                else
                    $ServiceQuery->allow_before_image=0;

                if(!empty($request->allow_after_image))
                    $ServiceQuery->allow_after_image = $request->allow_after_image;
                else
                    $ServiceQuery->allow_after_image=0;
                /*if($request->hasFile('picture')) {
                    $ServiceQuery->picture = Helper::upload_file($request->file('picture'), 'xuber/services', 'service-'.time().'.png');
                }*/
                $ServiceQuery->save();

                //Send message to socket
                $requestData = ['type' => 'SERVICE_SETTING'];
                app('redis')->publish('settingsUpdate', json_encode( $requestData ));

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else{
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
            }
        }catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // ONLY STATUS UPDATE ADDED INSTEAD OF HARD DELETE // return $this->removeModel($id);
        $SubCategory = Service::findOrFail($id);
        if($SubCategory){
            $SubCategory->active_status = 2;
            $SubCategory->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } else{
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
        }
    }

    public function subcategoriesList($categoryId)
    {
        $subCategories = ServiceSubcategory::select('id','service_subcategory_name','service_subcategory_status')
        ->where(['service_subcategory_status'=>1,'service_category_id'=>$categoryId])->get();
        return Helper::getResponse(['data' => $subCategories]);
    }

    public function getServicePriceCities($id)
    {
        $admin_service = AdminService::where('admin_service','service')->where('company_id',Auth::user()->company_id)->value('id');
        if($admin_service){
            $cityList = CompanyCountry::with('country','companyCountryCities')->where('company_id',Auth::user()->company_id)->where('status',1)->get();
        }
        return Helper::getResponse(['data' => $cityList]);
    }

    public function servicePricePost(Request $request)
    {
        \Log::info('price');
        \Log::info($request->all());
        // dd($request->all());
         $this->validate($request, [
            'country_id' => 'required',
            'city_id' => 'required',
            'base_fare' => 'required|numeric',
            'per_miles' => 'sometimes|nullable|numeric',
            'per_mins' => 'sometimes|nullable|numeric',
            'base_distance' => 'sometimes|nullable|numeric',
            'fare_type' => 'required|in:FIXED,HOURLY,DISTANCETIME',
            'commission' => 'required|nullable|numeric',
            'tax' => 'numeric',
            'allow_quantity' => 'sometimes', 
            'max_quantity' => 'sometimes|nullable|numeric',           
        ]);       
        try{
            if($request->service_price_id !=''){
                $servicePrice = ServiceCityPrice::findOrFail($request->service_price_id);
            }else{
                $servicePrice = new ServiceCityPrice;
            }
           
            $servicePrice->company_id = Auth::user()->company_id;  
            $servicePrice->base_fare = $request->base_fare; 
            $servicePrice->country_id = $request->country_id;  
            $servicePrice->city_id = $request->city_id;  
            $servicePrice->service_id = $request->service_id;  
            $servicePrice->fare_type = $request->fare_type;  
            $servicePrice->commission = $request->commission;
            $servicePrice->tax = $request->tax;
            $servicePrice->fleet_commission = $request->fleet_commission ? $request->fleet_commission : 0;
            // $servicePrice->fleet_commission = $request->fleet_commission;
            if(!empty($request->per_miles))
                $servicePrice->per_miles = $request->per_miles;
            else
                $servicePrice->per_miles=0;

            if(!empty($request->per_mins))
                $servicePrice->per_mins = $request->per_mins;
            else
                $servicePrice->per_mins=0;

            if(!empty($request->base_distance))
                $servicePrice->base_distance = $request->base_distance;
            else
                $servicePrice->base_distance=0;

            if(!empty($request->allow_quantity))
                $servicePrice->allow_quantity = $request->allow_quantity;
            else
                $servicePrice->allow_quantity=0;

            if(!empty($request->max_quantity))
                $servicePrice->max_quantity = $request->max_quantity;
            else
                $servicePrice->max_quantity=0;

            $servicePrice->save();
           return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
           return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function getServicePrice($service_id,$city_id){
        \Log::info($service_id);
        \Log::info($city_id);
        $serviceCityPrice = ServiceCityPrice::where(['company_id'=>Auth::user()->company_id,
        'service_id'=>$service_id,'city_id'=>$city_id])->first();
        \Log::info($serviceCityPrice);
        if($serviceCityPrice){
            return Helper::getResponse(['data' =>$serviceCityPrice,'price'=>'']);
        }
        return Helper::getResponse(['data' =>'','price'=>'']);
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = Service::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->service_status = 0;
                }else{
                    $datum->service_status = 1;
                }
            }
            $datum->save();
           
           
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

     public function dashboarddata($id)
    {
      try{
          
          $completed= ServiceRequest::where('country_id',$id)->where('status','COMPLETED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
          });
          $cancelled= ServiceRequest::where('country_id',$id)->where('status','CANCELLED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
          });

          $month=array('01','02','03','04','05','06','07','08','09','10','11','12');
           
          foreach($month as $k => $v){
              if(empty($completed[$v])){
                $complete[]=0;
              }else{
                $complete[]=count($completed[$v]);
              }

              if(empty($cancelled[$v])){
                $cancel[]=0;
              }else{
                $cancel[]=count($cancelled[$v]);
              }
          }
           
          $overall= ServiceRequest::where('country_id',$id)->where('status','COMPLETED')->where('company_id',Auth::user()->company_id)->count();

          $data['cancelled_data']=$cancel;
          $data['completed_data']=$complete;
          $data['max']=max($complete);
          $data['overall']=$overall;
          if(max($complete) < max($cancel)){
            $data['max']=max($cancel);
          }
          
          
          return Helper::getResponse(['status' => 200,'data'=> $data]);

         }
         catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
      
   }

}
