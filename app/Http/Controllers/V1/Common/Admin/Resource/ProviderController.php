<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Models\Common\Provider;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\ProviderService;
// use App\Models\Transport\RideDeliveryVehicle;
use App\Services\SendPushNotification;
use App\Models\Common\ProviderDocument;
use App\Models\Common\Document;
use App\Models\Common\AdminService;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCountry;
use App\Models\Common\Setting;
use DB;
use Auth;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Services\ReferralResource;
use App\Services\Transactions;

class ProviderController extends Controller
{
    use Actions;
    use Encryptable;
    
    private $model;
    private $request;
   /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Provider $model)
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
        $datum = Provider::with('admin')->where('company_id', Auth::user()->company_id)->orderby('id', 'DESC');

        if(Auth::user()->type=='FLEET') {
            $datum->where('admin_id', Auth::user()->id);  
        }
        if($request->statuslist !="ALL" && $request->statuslist !="")
        {   
            if($request->statuslist ==1)
                $datum->where('status', "APPROVED");
            else
                $datum->where('status',"!=", "APPROVED");
        }

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
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'country_code' => 'required|max:25',
            'email' => 'required|email|max:255',
            'mobile' => 'required|digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
            'country_id' => 'required',
            'city_id' => 'required',
        ]);

        $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
        ]);


        $company_id=Auth::user()->company_id;
        $email=$request->email;
        $mobile=$request->mobile;
        

        $this->validate($request, [          
            'email' =>[ Rule::unique('providers')->where(function ($query) use($email,$company_id) {
                            return $query->where('email', $email)->where('company_id', $company_id);
                         }),
                       ],
            'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id) {
                            return $query->where('mobile', $mobile)->where('company_id', $company_id);
                         }),
                       ],
        ]);

        try{
            
            $request->merge([
                'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
            ]);    

            // $provider = $request->all();
            // $request->request->add(['company_id' => \Auth::user()->company_id]);
            // $provider['password'] =Hash::make($request->password);
            $provider = new Provider;
            $provider->admin_id = Auth::user()->id;  
            $provider->company_id = Auth::user()->company_id;  
            $provider->first_name = $request->first_name; 
            $provider->last_name = $request->last_name; 
            $provider->email = $request->email;  
            $provider->country_code = $request->country_code;                                      
            $provider->mobile = $request->mobile; 
            $provider->password = Hash::make($request->password);  
            $provider->country_id = $request->country_id;                     
            $provider->city_id = $request->city_id;  
            if($request->hasFile('picture')) {
                $provider['picture'] = Helper::upload_file($request->file('picture'), 'provider/profile');
            }  

            $country = CompanyCountry::where('company_id',Auth::user()->company_id)->where('country_id', $request->country_id)->first();
            $provider->currency_symbol = $country->currency;
            $provider->referral_unique_id=(new ReferralResource)->generateCode($company_id);

            $provider->save();

            $provider->qrcode_url = Helper::qrCode(json_encode(["country_code" => $request->country_code, 'phone_number' => $request->mobile]), $provider->id.'.png', Auth::user()->company_id);
            $provider->save();

            $request->merge(["body" => "registered"]);
            $this->sendUserData($request->all());
           

            //$provider = Provider::create($provider);
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $provider = Provider::findOrFail($id);
             $provider['city_data']=CompanyCity::where("country_id",$provider['country_id'])->with('city')->get();
            return Helper::getResponse(['data' => $provider]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'country_code' => 'required|max:25',
            'email' => $request->email != null ?'sometimes|required|email|max:255':'',
            'mobile' => $request->mobile != null ?'sometimes|digits_between:6,13':'',
            'country_id' => 'required',
            'city_id' => 'required',
        ]);

        $company_id=Auth::user()->company_id;
        if($request->has('email') && $request->has('mobile')) {

            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
            ]);


            
            $email=$request->email;
            $mobile=$request->mobile;
            
            $this->validate($request, [          
                'email' =>[ Rule::unique('providers')->where(function ($query) use($email,$company_id,$id) {
                                return $query->where('email', $email)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                             }),
                           ],
                'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id,$id) {
                                return $query->where('mobile', $mobile)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                             }),
                           ],
            ]);
        }


        try {

            if($request->has('email') && $request->has('mobile')) {
                $request->merge([
                    'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                    'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
                ]);
            }

            $provider = Provider::findOrFail($id);   
            $provider->first_name = $request->first_name;
            $provider->last_name = $request->last_name;
            if($request->has('email') && $request->has('mobile')) {
                $provider->country_code = $request->country_code;
                $provider->mobile = $request->mobile;
                $provider->email = $request->email;
            }
            $provider->password = Hash::make($request->password);  
            $provider->country_id = $request->country_id;                     
            $provider->city_id = $request->city_id;  
            if($request->hasFile('picture')) {
                $provider['picture'] = Helper::upload_file($request->file('picture'), 'provider/profile');
            } 

            $country = CompanyCountry::where('company_id',Auth::user()->company_id)->where('country_id', $request->country_id)->first();
            $provider->currency_symbol = $country->currency;
             
            $provider->save();

             
            $request->merge(["body"=>"updated"]);

            if($request->has('email') && $request->has('mobile')) {
                 
            // $this->sendUserData($request->all());
            // return Helper::getResponse(['status' => 200, 'message' => 'kavi']);
            }

 \Log::info('success');
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);  
        } 
        catch (\Throwable $e) {
            \Log::info($e);
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = Provider::findOrFail($id);
            //if($datum->is_service==1 && $datum->is_document==1 && $datum->is_bankdetail==1 ){
                if($request->has('status')){
                   if($request->status == "Enable"){
                        //$datum->status = "APPROVED";
                        $datum->activation_status = "1";
                        $datum->is_online = "1";
                    }else{
                        //$datum->status = "BANNED";
                        $datum->activation_status = "0";
                        $datum->is_online = "0";
                    }
                }
                $datum->save();

                if($request->status == "Disable"){
                    $status = "Enable";
                }else{
                    $status = "Disable";
                    if($datum->jwt_token != null) {
                        Auth::guard('provider')->setToken($datum->jwt_token);
                        try {
                            Auth::guard('provider')->invalidate();
                        } catch (\Throwable $e) { }
                        $datum->jwt_token = null;
                        $datum->save();
                    }
                }

                $datum['body'] = $status;
                
                //$this->sendUserData($datum);

                (new SendPushNotification)->updateProviderStatus($datum->id, 'provider', 'Provider '.$status.'d successfully', 'Account Info', json_encode(['service' => $datum->is_service, 'document' => $datum->is_document, 'bank' => $datum->is_bankdetail]));

                $requestData = ['type' => 'PROVIDER', 'room' => 'room_provider_'.$datum->company_id.'_'.$id,
                 'id' => $id ];
                app('redis')->publish('providerUpdate', json_encode( $requestData ));

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);
           //} else{
             //return Helper::getResponse(['status' => 200, 'message' =>'Status Not Updated Contact Admin']);
          // }
            

        } 


        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function approveStatus(Request $request, $id)
    {
        
        try {
            $datum = Provider::findOrFail($id);
            $provider_doc=ProviderDocument::where('provider_id',$id)->count();
            $provider_status=ProviderDocument::where('provider_id',$id)->where('status','ACTIVE')->count();


            if($datum->is_service!=1){
               return Helper::getResponse(['status' => 200, "data"=>['status'=>1],'message' => 'Please Add Service']); 
            }else if($datum->is_document!=1){
               return Helper::getResponse(['status' => 200, "data"=>['status'=>1],'message' => 'Please Add Document']); 
            }else if($datum->is_bankdetail!=1){
               return Helper::getResponse(['status' => 200, "data"=>['status'=>1],'message' => 'Please Add Bankdetails']); 
            }else if(empty($datum->zone_id)){
                return Helper::getResponse(['status' => 200, "data"=>['status'=>1],'message' => 'Please Select Zone For the Provider ']); 
            }
             
            if($datum->is_service==1 && $datum->is_document==1 && $datum->is_bankdetail==1 && ($provider_doc==$provider_status)  ){
                $datum->status="APPROVED";
                $datum->activation_status="1";
                $datum->save();
                $datum['body'] = "APPROVED";
                //$this->sendUserData($datum);

                (new SendPushNotification)->updateProviderStatus($datum->id, 'provider', trans('admin.activation_status'), 'Account Info', json_encode(['service' => $datum->is_service, 'document' => $datum->is_document, 'bank' => $datum->is_bankdetail])); 
                 $requestData = ['type' => 'PROVIDER', 'room' => 'room_provider_'.$datum->company_id.'_'.$id,
                 'id' => $id ];
                app('redis')->publish('providerUpdate', json_encode( $requestData ));


                return Helper::getResponse(['status' => 200,"data"=>['status'=>0],'message' => trans('admin.activation_status')]);
           } else{
             return Helper::getResponse(['status' => 200,"data"=>['status'=>1], 'message' =>'Status não atualizado Administrador de contato']);
           }
            

        } 


        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function zoneStatus(Request $request, $id)
    {
        
        try {
            $datum = Provider::where('id',$id)->update(['zone_id'=>$request->zone_id]);
            
             return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $datum = Provider::findOrFail($id);
        $settings = Setting::where('company_id', Auth::user()->company_id)->first()->settings_data->site;

        if( !empty($settings->send_email) && $settings->send_email == 1) {

            $datum['body'] = "deleted";
            $datum['mail_driver'] = $settings->mail_driver;
            
            $this->sendUserData($datum);
        }

        return $this->removeModel($id);
    }

    //For vehicle service type
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function vehicle_type(Request $request)
    {
        $this->validate($request, [
            'vehicle_service_id' => 'required',
            'vehicle_model' => 'required',
            'vehicle_no' => 'required',
        ]);
        try {
            $provider_vehicle = new ProviderVehicle;
            $provider_vehicle->company_id = Auth::user()->company_id; 
            $provider_vehicle->provider_id =$request->id;
            $provider_vehicle->vehicle_service_id = $request->vehicle_service_id;
            $provider_vehicle->vehicle_model = $request->vehicle_model;
            $provider_vehicle->vehicle_no = $request->vehicle_no;
            $provider_vehicle->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);  
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function ProviderService($id)
    {
        $ProviderService = ProviderService::with('providervehicle','admin_service')->where('company_id',Auth::user()->company_id)->where('provider_id',$id)->get();
        return Helper::getResponse(['data' => $ProviderService]);
    }
    public function service_on($id)
    {
        $service_on = ProviderVehicle::where('company_id',Auth::user()->company_id)->where('vehicle_service_id',$id)->update(['status' => 1]);
        return Helper::getResponse(['status' => 200, 'message' => trans('admin.active_status')]); 
    }
    public function service_off($id)
    {
        $service_on = ProviderVehicle::where('company_id',Auth::user()->company_id)->where('vehicle_service_id',$id)->update(['status' => 0]);
        return Helper::getResponse(['status' => 200, 'message' => trans('admin.deactive_status')]); 
    }
    public function deleteservice($id)
    {
        $service_on = ProviderService::destroy($id);
        return Helper::getResponse(['status' => 200, 'message' => trans('admin.delete')]); 
    }
    public function provider_services($admin_service)
    {
        $services = [];

        if($admin_service == "TRANSPORT") {
            try {
                $services = \App\Models\Transport\RideType::with('servicelist')->where('company_id', Auth::user()->company_id)->where('status', 1)->get();
                //$services = \App\Models\Transport\RideDeliveryVehicle::select('id', 'vehicle_name AS name')->where('company_id', Auth::user()->company_id)->where('status', 1)->get();
            } catch(\Throwable $e) { }
        } else if($admin_service == "SERVICE") {
            try {
                $services = \App\Models\Service\ServiceCategory::with('subcategories.service')->where('company_id', Auth::user()->company_id)->get();
                //$services = \App\Models\Transport\RideDeliveryVehicle::select('id', 'vehicle_name AS name')->where('company_id', Auth::user()->company_id)->where('status', 1)->get();
            } catch(\Throwable $e) { }
        }

        return Helper::getResponse(['data' => $services ]);
    }

    
    //For document list
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */

     public function providerdocument(Request $request,$id)
     {
        $Provider = ProviderDocument::with('document','document.service_categories')->where('company_id',Auth::user()->company_id)->where('provider_id',$id)->paginate(10);
        return Helper::getResponse(['data' => $Provider]);
     }
     //For view document list
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider for view document
     * @return \Illuminate\Http\Response
     */
     public function view_document(Request $request,$id)
     {
         $provider_document = ProviderDocument::with('document','provider')
                              ->where('company_id',Auth::user()->company_id)                          
                              ->where('id',$id)
                             ->get();
         return Helper::getResponse(['data' => $provider_document]);
     }
     //Approve the provider image
    public function approve_image($id)
    {
        $service_on = ProviderDocument::where('id',$id)->update(['status' => 'ACTIVE']);
        return Helper::getResponse(['status' => 200, 'message' => trans('admin.deactive_status')]); 
    }
    //Delete the provider image
    public function delete_view_image(Request $request,$id)
    {
        Provider::where('id',$request->provider_id)->update(['is_document' => 0]);
        $service_on = ProviderDocument::where('company_id',Auth::user()->company_id)->where('id',$id)->delete();
        return Helper::getResponse(['status' => 200, 'message' => trans('admin.delete')]); 
    }

    public function approve_all($type)
    {    
          try {
              $document=Document::where('type',$type)->select('id')->get();
              ProviderDocument::whereIn('document_id',$document)->update(['status'=>'ACTIVE']);
               return Helper::getResponse(['status' => 200, 'message' => trans('admin.deactive_status')]); 
          }catch(\Throwable $e) {
             return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);

        }
    }

    public function provider_total_deatils(Request $request,$id){
        try {
            $provider = Provider::with('totaldocument','totaldocument.document','totaldocument.document.service_categories','providerservice','providerservice.vehicle','providerservice.admin_service')->where('id',$id)->get();
             return Helper::getResponse(['status' => 200,'data' => $provider]); 
       } catch(\Throwable $e) {
             return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);

        }
       
    }

    public function addamount(Request $request,$id){
        try{
            $transaction['message']='Admin Added Amount';
            $transaction['amount']=$request->amount;
            $transaction['company_id']=Auth::user()->company_id;
            $transaction['id']=$id;
            (new Transactions)->AdminAddAmountCreditDebit($transaction,0);
            (new SendPushNotification)->adminAddamount($id, 'provider', trans('admin.admin_add_amount'),$request->amount);
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.add_amount')]); 

        }  catch(\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

   


    public function searchprovider(Request $request,$id){
        $provider = Provider::where('zone_id',$id)->where('wallet_balance','>',1)
       ->where('first_name', 'like', "%" . $request->term . "%")->Orwhere('last_name', 'like', "%" . $request->term . "%")
       ->select(\DB::raw("CONCAT(first_name,' ',last_name,'(',id,')') AS label"),\DB::raw("CONCAT(first_name,' ',last_name,'(',id,')') AS value"),'id','wallet_balance','zone_id','first_name','last_name')->get()->toArray();
       return $provider;
    }

}
