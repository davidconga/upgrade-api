<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Common\AdminService;
use App\Models\Common\CustomPush;
use App\Traits\Actions;
use App\Models\Common\Admin;
use App\Models\Common\Provider;
use App\Models\Common\Setting;
use App\Models\Common\Rating;
use App\Models\Service\Service;
use App\Traits\Encryptable;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use App\Services\SendPushNotification;
class AdminController extends Controller
{
    use Actions, Encryptable;

    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Admin $model)
    {
        $this->model = $model;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show_profile()
    {
        $account_setting = Admin::where('id',Auth::user()->id)->where('company_id',\Auth::user()->company_id)->first();
        return Helper::getResponse(['data' => $account_setting]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'email' => 'required|max:255|email',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try{
            //$admin = Auth::guard('admin')->user();
            $admin = Admin::where('id',Auth::user()->id)->where('company_id',\Auth::user()->company_id)->first();
            $admin->name = $request->name;
            $admin->email = $request->email;
            if($request->hasFile('picture')) {
                $admin->picture = Helper::upload_file($request->file('picture'), 'admin/picture');
            }
            $admin->save();

            return Helper::getResponse(['status' => 200,'data' => $admin, 'message' => trans('admin.update')]);
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
    public function password()
    {
        $password = Admin::where('id',Auth::user()->id)->where('company_id',\Auth::user()->company_id)->first();
        return Helper::getResponse(['data' => $password]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

           $Admin = Admin::where('id',Auth::user()->id)->where('company_id',\Auth::user()->company_id)->first();

            if(password_verify($request->old_password, $Admin->password))
            {
                $Admin->password = Hash::make($request->password);
                $Admin->save();
            }
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        }  catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function admin_service(Request $request)
    {
        $admin_service = AdminService::where('company_id',Auth::user()->company_id)->where('status',1)->get();
        return Helper::getResponse(['data' => $admin_service]);
    }

    public function child_service(Request $request, $id)
    {
        $services = Service::where('service_subcategory_id', $id)->get();
        return Helper::getResponse(['data' => $services]);
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type=null,Request $request)
    {
        $roles = Role::get();
        if($type=="Admin"){
            $datum = Admin::whereHas('roles', function ($query) use($type) {
                        $query->where('roles.id','>',5);
                        })->where('company_id',\Auth::user()->company_id);
        }else{
            $datum = Admin::whereHas('roles', function ($query) use($type) {
                    $query->where('roles.name', $type);
                    })->where('company_id',\Auth::user()->company_id);
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
            'name' => 'required|max:20',
            'email' => 'required|email|max:255',
            'mobile' => 'required|digits_between:6,13',
            // 'email' => 'required|unique:accounts,email|email|max:255',
            'password' => 'required|min:6|confirmed|max:15',
            'role' => 'required'

        ],['role.required'=>'Please create a role for this Admin. ']);

        
        $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET'))
        ]);

        $company_id = Auth::user()->company_id;
        $email = $request->email;
        $mobile=$request->mobile;
        $type = $request->type;
        
        $this->validate($request, [          
            'email' =>[ Rule::unique('admins')->where(function ($query) use($email,$company_id, $type) {
                            return $query->where('email', $email)->where('company_id', $company_id)->where('type', $type);
                         }),
                       ],
            'mobile' =>[ Rule::unique('admins')->where(function ($query) use($mobile,$company_id) {
                            return $query->where('mobile', $mobile)->where('company_id', $company_id);
                         }),
                       ],
        ]);

       
        try{

            $request->merge([
                'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET'))
            ]); 

            $request->request->add(['company_id' => \Auth::user()->company_id]);
            $admin = $request->all();

            $admin['password'] = Hash::make($request->password);
            $admin['type'] = $type;
            $admin['country_code'] = $request->country_code;
            $admin = Admin::create($admin);
            $admin->assignRole($request->input('role'));
            
            if($request->hasFile('picture')) {
                $admin->picture = Helper::upload_file($request->file('picture'), 'admin/picture');
            }
            $admin->country_code = $request->country_code;
            $admin->save();
            $request->merge(["body" => "registered"]);
            $this->sendUserData($request->all());
      
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $admin
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $admin = Admin::findOrFail($id);
            $admin->roles->pluck('id','id');
            $admin->role = $admin->roles[0]->id;

           return Helper::getResponse(['data' => $admin]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:20',
            'email' => $request->email != null ?'sometimes|required|email|max:255':'',
            'mobile' => 'digits_between:6,13',
            // 'email' => 'require|d|unique:dispatchers,email|email|max:255',
        ]);

        if($request->has('email')) {
            $request->merge(['email' => $this->cusencrypt($request->email, env('DB_SECRET'))]);
        }
        if($request->has('mobile')) {
            $request->merge(['mobile' => $this->cusencrypt($request->mobile, env('DB_SECRET'))]);
        }

        $company_id=Auth::user()->company_id;
        if($request->has('email')) {
        $email = $request->email;
        }
        if($request->has('mobile')) {
        $mobile = $request->mobile;
        }
        $type = $request->type;


        if($request->has('email')) {
        $this->validate($request, [
            'email' =>[ Rule::unique('admins')->where(function ($query) use($email,$company_id,$type,$id) {
                return $query->where('email', $email)->where('company_id', $company_id)->where('type', $type)->whereNotIn('id', [$id]);
                })
            ], 
        ]);
        } 
        if($request->has('mobile')) {
        $this->validate($request, [
            'mobile' =>[ Rule::unique('admins')->where(function ($query) use($mobile,$company_id,$id) {
                    return $query->where('mobile', $mobile)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                 }),
               ]
        ]);
        }

        
      
        try{

            if($request->has('email')) {
                $request->merge(['email' => $this->cusdecrypt($request->email, env('DB_SECRET'))]);
            }
            if($request->has('mobile')) {
                $request->merge(['mobile' => $this->cusdecrypt($request->mobile, env('DB_SECRET'))]);
            }

            $admin = Admin::findOrFail($id);
            $admin->name = $request->name;
            if($request->has('mobile')) {
            $admin->country_code = $request->country_code;
            $admin->mobile = $request->mobile;
            }
            if($request->has('email')) {
            $admin->email = $request->email;
            }
            
            $admin->type = $request->type;
            if($request->password != '' || $request->password != null){

                $admin->password = Hash::make($request->password);
            }
            $admin->syncRoles($request->input('role'));
           
            if($request->hasFile('picture')) {
                $admin->picture = Helper::upload_file($request->file('picture'), 'admin/picture');
            }
            if($request->has('country_id')) {
            $admin->country_id = $request->country_id;
             }
             if($request->has('city_id')) {
            $admin->city_id = $request->city_id;
             }
             if($request->has('country_id')) {
            $admin->zone_id = $request->zone_id;
             }
             if($request->has('company_name')) {
            $admin->company_name = $request->company_name;
             }if($request->has('commision')) {
            $admin->commision = $request->commision;
             }
            $admin->save();

            $request->merge(["body"=>"updated"]);
            if($request->has('email')) {
            $this->sendUserData($request->all());
            }

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = Admin::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->status = 0;
                }else{
                    $datum->status = 1;
                }
            }
            $datum->save();

            if($request->status == 1){
                $status = "disabled";
            }else{
                $status = "enabled";
            }

            $datum['body'] = $status;
            $this->sendUserData($datum);

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\admin  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $datum = Admin::findOrFail($id);
       
        $datum['body'] = "deleted";
        $this->sendUserData($datum);

        return $this->removeModel($id);
    }
    public function role_list()
    {
        
        $roles = Role::where("id",'>',"5")->where('company_id','=',NULL)->orwhere('company_id',\Auth::user()->company_id)->get();
        return Helper::getResponse(['data' => $roles]);
    }
    
    public function userReview(Request $request)
    {
        try {
            $datum = Rating::where([['company_id', Auth::user()->company_id],['user_id', '!=', 0]])->with('user', 'provider');
         
            if($request->has('search_text') && $request->search_text != null) {
                $datum->Usersearch($request->search_text);
            }
    
            if($request->has('order_by')) {
                $datum->orderby($request->order_by, $request->order_direction);
            }
    
            $data = $datum->paginate(10);

             foreach ($data as $key => $value){
             
                    try {
                        $servicedata=\App\Models\Service\ServiceRequest::where('id',$value['request_id'])->first();  
                        $data[$key]['booking_id']=!empty($servicedata)?$servicedata->booking_id:"XXXXXX";

                    }catch (\Throwable $e) {
                        return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
                    }

              
            }
    
            return Helper::getResponse(['data' => $data]);

        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function providerReview(Request $request)
    {
        try {
           
            $is_fleet = Auth::user()->hasRole('FLEET');
            $logged_id = Auth::user()->id;
            $datum = Rating::where('company_id', Auth::user()->company_id)->with('user', 'provider')
            ->whereHas('provider', function($query) use ($is_fleet, $logged_id){
                if($is_fleet) {
                    $query->where('admin_id',$logged_id);
                }
            });
            if($request->has('search_text') && $request->search_text != null) {
                $datum->Providersearch($request->search_text);
            }
    
            if($request->has('order_by')) {
                $datum->orderby($request->order_by, $request->order_direction);
            }
    
            $data = $datum->paginate(10);

            foreach ($data as $key => $value){
            
                    try {
                        $servicedata=\App\Models\Service\ServiceRequest::where('id',$value['request_id'])->first();  
                        $data[$key]['booking_id']=!empty($servicedata)?$servicedata->booking_id:"XXXXXX";
                        
                    }catch (\Throwable $e) {
                        return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
                    }

             
            }

            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function heatmap(Request $request) {
        try {

            $services = [];

            $type = strtoupper($request->type);



            if($type == 'TRANSPORT') {
                try {
                    $services = \App\Models\Transport\RideRequest::whereIn('status', ['SEARCHING'])->orderBy('id','desc')->get();
                } catch(\Throwable $e) {  }
            } else if($type == 'ORDER') {
                try {
                    $services = \App\Models\Order\StoreOrder::whereIn('status', ['SEARCHING'])->orderBy('id','desc')->get();
                } catch(\Throwable $e) { }
            } else if($type == 'SERVICE') {
                try {
                    $services = \App\Models\Service\ServiceRequest::whereIn('status', ['SEARCHING'])->orderBy('id','desc')->get();
                } catch(\Throwable $e) { }
            }
            
            $data = [];
            foreach ($services as $service) {
                $data[] = ['lat' => $service->s_latitude, 'lng' => $service->s_longitude];
            }
            return Helper::getResponse(['data' => $data]);
        }catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


    public function godsview(Request $request)
    {
       try{

            $type = strtoupper($request->type);
            $status = $request->status;
           if($type == 'SERVICE') {
                if($request->status == 'STARTED' || $request->status == 'ARRIVED' || $request->status == 'PICKEDUP' || $request->status == 'DROPPED') {
                    $providers = Provider::with(['request'])
                        ->whereHas('request', function ($query) use($status) {
                            $query->where('status', $status)
                            ->where('admin_service','SERVICE');
                        })->select('id', 'first_name', 'last_name', 'mobile', 'email', 'picture', 'status', 'latitude', 'longitude', 'is_assigned','is_online')->get();
                } else if($request->status == 'ACTIVE') {
                    $providers = Provider::with(['providerservice'=>function($q){
                        $q->where('admin_service','SERVICE')->whereNotNull('service_id')
                        ->select('id','provider_id','admin_service','provider_vehicle_id','ride_delivery_id','category_id');
                        },'service' => function($q){
                                $q->select('id','vehicle_name');
                        },'providerservice.mainservice','request', 'service'])->whereHas('service', function ($query) {
                            $query->where('status', 'active');
                        })->select('id', 'first_name', 'last_name', 'mobile', 'email', 'picture', 'status', 'latitude', 'longitude', 'is_assigned','is_online')->where('is_online',1)->where('is_assigned',0)->get();
                } else {

                    $providers = Provider::with(['providerservice'=>function($q){
                        $q->where('admin_service','SERVICE')->whereNotNull('service_id')
                        ->select('id','provider_id','admin_service','provider_vehicle_id','ride_delivery_id','category_id');
                    },'providerservice.mainservice','service', 'service.vehicle', 'request', 'service'])->select('id', 'first_name', 'last_name', 'mobile', 'email', 'picture', 'status', 'latitude', 'longitude', 'is_assigned','is_online')->get();
                }


               /* $providers = Provider::with(['request'])
                ->whereHas('request', function ($query) use($status) {
                    $query->where('status', $status);
                })->select('id', 'first_name', 'last_name', 'mobile', 'email', 'picture', 'status', 'latitude', 'longitude', 'is_assigned')->get();*/
                
            }           
            
            $locations = [];

            foreach ($providers as $provider) {
                $locations[] = ['name' => $provider->first_name." ".$provider->last_name, 'lat' =>  $provider->latitude, 'lng' => $provider->longitude, 'car_image' =>  'asset/img/cars/car.png'];
            }
            return Helper::getResponse(['data' => ['providers' => $providers, 'locations' => $locations] ]);
        }
        catch(Exception $e){
             return Helper::getResponse(['status' => 500,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function SendCustomPush($CustomPush){

        try{

            \Log::notice("Starting Custom Push");

            $Push = CustomPush::findOrFail($CustomPush);

            if($Push->send_to == 'USERS'){

                $Users = [];

                if($Push->condition == 'ACTIVE'){

                    if($Push->condition_data == 'HOUR'){

                        $Users = User::whereHas('trips', function($query) {
                            $query->where('created_at','>=',Carbon::now()->subHour());
                        })->get();
                        
                    }elseif($Push->condition_data == 'WEEK'){

                        $Users = User::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subWeek());
                        })->get();

                    }elseif($Push->condition_data == 'MONTH'){

                        $Users = User::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subMonth());
                        })->get();

                    }

                }elseif($Push->condition == 'RIDES'){

                    $Users = User::whereHas('trips', function($query) use ($Push){
                                $query->where('status','COMPLETED');
                                $query->groupBy('id');
                                $query->havingRaw('COUNT(*) >= '.$Push->condition_data);
                            })->get();


                }elseif($Push->condition == 'LOCATION'){

                    $Location = explode(',', $Push->condition_data);

                    $distance = config('constants.provider_search_radius', '10');
                    $latitude = $Location[0];
                    $longitude = $Location[1];

                    $Users = User::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                }


                foreach ($Users as $key => $user) {
                    (new SendPushNotification)->sendPushToUser($user->id, $Push->message);
                }

            }elseif($Push->send_to == 'PROVIDERS'){


                $Providers = [];

                if($Push->condition == 'ACTIVE'){

                    if($Push->condition_data == 'HOUR'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subHour());
                        })->get();
                        
                    }elseif($Push->condition_data == 'WEEK'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subWeek());
                        })->get();

                    }elseif($Push->condition_data == 'MONTH'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subMonth());
                        })->get();

                    }

                }elseif($Push->condition == 'RIDES'){

                    $Providers = Provider::whereHas('trips', function($query) use ($Push){
                               $query->where('status','COMPLETED');
                                $query->groupBy('id');
                                $query->havingRaw('COUNT(*) >= '.$Push->condition_data);
                            })->get();

                }elseif($Push->condition == 'LOCATION'){

                    $Location = explode(',', $Push->condition_data);

                    $distance = config('constants.provider_search_radius', '10');
                    $latitude = $Location[0];
                    $longitude = $Location[1];

                    $Providers = Provider::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                }


                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification)->sendPushToProvider($provider->id, $Push->message);
                }

            }elseif($Push->send_to == 'ALL'){

                $Users = User::all();
                foreach ($Users as $key => $user) {
                    (new SendPushNotification)->sendPushToUser($user->id, $Push->message);
                }

                $Providers = Provider::all();
                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification)->sendPushToProvider($provider->id, $Push->message);
                }

            }
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

}
