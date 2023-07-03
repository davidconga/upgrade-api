<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use App\Models\Common\Admin;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Common\UserRequest;
use App\Models\Common\RequestFilter;
use App\Models\Common\AdminService;
use App\Models\Common\Setting;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\User;
use App\Models\Common\Provider;
use App\Services\V1\Transport\Ride;
use Spatie\Permission\Models\Role;
use App\Models\Common\ProviderService;
use App\Services\SendPushNotification;
use App\Models\Service\Service;
use App\Models\Service\ServiceRequest;
use App\Models\Common\CompanyCountry;
use App\Models\Transport\RideType;
use App\Models\Service\ServiceCategory;
use App\Models\Service\ServiceCancelProvider;
use App\Models\Transport\RideRequest;
use Carbon\Carbon;
use App\Traits\Actions;
use App\Traits\Encryptable;
use Exception;
use Auth;
use DB;
use Log;

class DispatcherController extends Controller
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
    public function __construct(Admin $model)
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
        $datum = Admin::where('company_id', Auth::user()->company_id);

        $column_name = $datum->first()->toArray();

        $columns = (count($column_name) > 0) ? array_keys($column_name) : [];

        if($request->has('search_text') && $request->search_text != null) {
            $datum->where(function ($query) use($columns, $request) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'LIKE', "%".$request->search_text."%");
                }
            });
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
            'name' => 'required|max:255',
            'email' => 'required|unique:dispatchers,email|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        try{
            $request->request->add(['company_id' => \Auth::user()->company_id]);
            $request->request->add(['parent_id' => \Auth::user()->id]);
            $Dispatcher = $request->all();
            $Dispatcher['password'] = Hash::make($request->password);   
            $Dispatcher = Admin::create($Dispatcher);

            $role = Role::where('name', 'DISPATCHER')->first();

            if($role != null) $Dispatcher->assignRole($role->id);

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $dispatcher = Admin::findOrFail($id);
            return Helper::getResponse(['data' => $dispatcher]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function trips(Request $request)
    {
        $settings = json_decode(json_encode(Setting::where('company_id', Auth::user()->company_id)->first()->settings_data));

        $siteConfig = $settings->site;

        $trips = UserRequest::with('user', 'provider', 'service')->orderBy('id','desc');

        if($request->type == "SEARCHING"){
            $trips = $trips->where('status',$request->type);
        }else if($request->type == "RECEIVED"){
            $trips = $trips->where('status',$request->type);
        }else if($request->type == "CANCELLED"){
            $trips = $trips->where('status',$request->type);
        }else if($request->type == "ASSIGNED"){
            $trips = $trips->whereNotIn('status',['SEARCHING', 'ORDERED', 'RECEIVED','SCHEDULED','CANCELLED','COMPLETED']);
        }
        
        $trips = $trips->get();

        return Helper::getResponse(['data' => $trips]);
    }


    


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|unique:dispatchers,email|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

            $dispatcher = Admin::findOrFail($id);
            $dispatcher->name = $request->name;
            $dispatcher->email = $request->email;
            $dispatcher->password = $request->password;
            $dispatcher->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->removeModel($id);
    }

    public function providers(Request $request)
    {

        if($request->has('latitude') && $request->has('longitude')) {

            $settings = json_decode(json_encode(Setting::where('company_id', Auth::user()->company_id)->first()->settings_data));
            $siteConfig = $settings->site;
             if($request->has('provider_service_id')) { 
                  $ActiveProviders = ProviderService::where('company_id', Auth::user()->company_id)->where('ride_delivery_id', $request->provider_service_id)->where('admin_service','TRANSPORT')->get()
                    ->pluck('provider_id');
                 $transportConfig = $settings->transport;
                 $distance = isset($transportConfig->provider_search_radius) ? $transportConfig->provider_search_radius : 10;   
                 $admin_service = "TRANSPORT";
             }



             if($request->has('store_type_id')) { 
                  $ActiveProviders = ProviderService::where('company_id', Auth::user()->company_id)->where('category_id', $request->store_type_id)->where('admin_service','ORDER')->get()
                    ->pluck('provider_id');
                  $orderConfig = $settings->order;
                  $distance = isset($orderConfig->store_search_radius) ? $orderConfig->store_search_radius : 100;   
                  $admin_service = "ORDER";
             }
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->where('is_online', 1)
                ->where('is_assigned', 0)
                ->where('wallet_balance' ,'>=',$siteConfig->provider_negative_balance)
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->with(['service' => function($query) use($admin_service,$request) {
                    if($admin_service== 'ORDER') {
                        $query->where('admin_service', 'ORDER');
                        $query->where('category_id', $request->store_type_id);
                    } else if($admin_service == 'TRANSPORT'){
                        $query->where('admin_service', 'TRANSPORT');
                        $query->where('ride_delivery_id', $request->provider_service_id);
                    }
                }, 'service.vehicle', 'service.ride_vehicle'])->get();

            return Helper::getResponse(['status' => 200, 'data' => $Providers]);
        }

        return null;
    }


    public function assign(Request $request)
    {
        //try {

            $type = 'common';

            //try {

                if($request->admin_service == "TRANSPORT" ) {
                    $newRequest = \App\Models\Transport\RideRequest::find($request->id);

                    $setting = Setting::where('company_id', $newRequest->company_id)->first();
                    //Send message to socket
                    $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id ];
                    app('redis')->publish('newRequest', json_encode( $requestData ));

                    $type = 'transport';

                } else if($request->admin_service == "ORDER") {
                    $newRequest = \App\Models\Order\StoreOrder::with('invoice', 'store.storetype')->where('id',$request->id)->first();
                    $setting = Setting::where('company_id', $newRequest->company_id)->first();
                    //Send message to socket
                    $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id ];
                    app('redis')->publish('newRequest', json_encode( $requestData ));

                    $type = 'order';
                }
           /* } catch(\Throwable $e) { }*/


            $Provider = Provider::findOrFail($request->provider_id);

            $newRequest->provider_id = $Provider->id;
            if($request->admin_service == "TRANSPORT")
            $newRequest->status = 'STARTED';
            else if($request->admin_service == "ORDER")
            $newRequest->status = 'PROCESSING';

            $newRequest->save();

            Provider::where('id',$newRequest->provider_id)->update(['is_assigned' =>'1']);
            //ProviderService::where('provider_id',$newRequest->provider_id)->update(['status' =>'riding']);

            (new SendPushNotification)->IncomingRequest($newRequest->provider_id, $type);
            if($request->admin_service == "TRANSPORT"){
                $rideType = RideType::find($newRequest->ride_type_id);
                $newRequest->request_method = $rideType->ride_name;
            }

            $user_request = UserRequest::where('request_id', $newRequest->id)->first();
            $user_request->provider_id = $newRequest->provider_id;
            $user_request->status = $newRequest->status;
            $user_request->request_data = json_encode($newRequest);

            $user_request->save();

            $filter = new RequestFilter;
            $filter->admin_service = $request->admin_service;
            $filter->request_id = $user_request->id;
            $filter->provider_id = $Provider->id; 
            $filter->company_id = Auth::user()->company_id; 
            $filter->save();

            return Helper::getResponse(['message' => trans('admin.dispatcher_msgs.request_assigned') ]);

        /*} catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage() ]);
        }*/
    }

    public function create_ride(Request $request)
    {

        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
            'country_code' => 'required|numeric',
            'country_id' => 'required',
            'city_id' => 'required',
        ]);

        $setting = Setting::where('company_id', Auth::user()->company_id)->first();

        $settings = json_decode(json_encode($setting->settings_data));

        $siteConfig = $settings->site;

        $transportConfig = $settings->transport;

        $country = Country::find($request->country_id);

        $state = State::where('country_id', $country->id)->first();

        $timezone = $state->timezone;

        $currency = $country->country_currency;

        $mobile = $this->cusencrypt($request->mobile,env('DB_SECRET'));
        $email = $this->cusencrypt($request->email,env('DB_SECRET'));

        try {
            $User = User::where('mobile', $mobile)->firstOrFail();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $email)->firstOrFail();
            } catch (Exception $e) {
                $User = User::create([
                    'company_id' => Auth::user()->company_id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'country_code' => $request->country_code,
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->mobile),
                    'country_id' => $request->country_id,
                    'state_id' => $state->id,
                    'city_id' => $request->city_id,
                    'currency' => $currency,
                    'payment_mode' => 'CASH'
                ]);
            }
        }

        if($request->schedule_date != "" && $request->schedule_time != "" ){
            try {

                $schedule_time = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');

                $CheckScheduling = \App\Models\Transport\RideRequest::where('status', 'SCHEDULED')
                        ->where('user_id', $User->id)
                        ->where('schedule_at', '>', strtotime($schedule_time." - 1 hour"))
                        ->where('schedule_at', '<', strtotime($schedule_time." + 1 hour"))
                        ->firstOrFail();
                
                return Helper::getResponse(['message' => trans('api.ride.request_scheduled'), 'error' => trans('api.ride.request_scheduled') ]);

            } catch (Exception $e) {
                // Do Nothing
            }
        }

        $distance = $transportConfig->provider_search_radius ? $transportConfig->provider_search_radius : 100;

        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;
        $service_type = $request->provider_service_id;

        $Providers = Provider::with('service')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id')
            ->where('status', 'approved')
            ->where('is_online', 1)
            ->where('is_assigned', 0)
            ->where('company_id', Auth::user()->company_id)
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->whereHas('service', function($query) use ($service_type){
                        $query->where('status','active');
                        $query->where('ride_delivery_id', $service_type);
                    })
            ->orderBy('distance','asc')
            ->get();

        if(count($Providers) == 0) {
            return Helper::getResponse(['status' => 422, 'message' => trans('api.ride.no_providers_found')]);
        } 

        try {
            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".$siteConfig->server_key;

            $json = Helper::curl($details);

            $details = json_decode($json, TRUE);

            $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

            $newRequest = new \App\Models\Transport\RideRequest;
            $newRequest->request_type = $transportConfig->broadcast_request==1?'AUTO':'MANUAL';
            $newRequest->company_id = Auth::user()->company_id;
            $newRequest->admin_service = 'TRANSPORT';
            $newRequest->booking_id = Helper::generate_booking_id('TRNX');

            $newRequest->user_id = $User->id;

            $newRequest->provider_service_id = $request->provider_service_id;
            $newRequest->ride_type_id = $request->ride_type_id;
            $newRequest->payment_mode = 'CASH';
            $newRequest->promocode_id = 0;
            
            $newRequest->status = 'SEARCHING';

            $newRequest->timezone = $timezone;
            $newRequest->currency = $currency;

            $newRequest->city_id = $request->city_id;

            $newRequest->s_address = $request->s_address ? $request->s_address : "";
            $newRequest->d_address = $request->d_address ? $request->d_address  : "";

            $newRequest->s_latitude = $request->s_latitude;
            $newRequest->s_longitude = $request->s_longitude;

            $newRequest->d_latitude = $request->d_latitude;
            $newRequest->d_longitude = $request->d_longitude;
            $newRequest->ride_delivery_id = $request->provider_service_id;


            $newRequest->track_distance = 1;
            
            $newRequest->track_latitude = $request->d_latitude ? $request->d_latitude : $request->s_latitude;
            $newRequest->track_longitude = $request->d_longitude ? $request->d_longitude : $request->s_longitude;

            if($request->d_latitude == null && $request->d_longitude == null) {
                $newRequest->is_drop_location = 0;
            }

            $newRequest->destination_log = json_encode([['latitude' => $newRequest->d_latitude, 'longitude' => $request->d_longitude, 'address' => $request->d_address]]);
            $newRequest->distance = $request->distance ? $request->distance  : 0;
            $newRequest->unit = isset($siteConfig->distance) ? $siteConfig->distance : 'Kms';

            $newRequest->is_track = "YES";

            $newRequest->otp = mt_rand(1000 , 9999);

            $newRequest->assigned_at = Carbon::now();
            $newRequest->route_key = $route_key;

            if($request->schedule_date != "" && $request->schedule_time != "" ){
                $newRequest->status = 'SCHEDULED';
                $newRequest->schedule_at = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');
                $newRequest->is_scheduled = 'YES';
            }

            if($newRequest->status != 'SCHEDULED') {
                if($transportConfig->manual_request == 0 && $transportConfig->broadcast_request == 0) {
                    //Log::info('New Request id : '. $newRequest->id .' Assigned to provider : '. $newRequest->provider_id);
                    //(new SendPushNotification)->IncomingRequest($Providers[0]->id);
                }
            }   

            $newRequest->save();

            $newRequest = RideRequest::with('ride', 'ride_type')->where('id', $newRequest->id)->first();

            if($transportConfig->manual_request == 1) {

                $admins = Admin::select('id')->get();

                foreach ($admins as $admin_id) {
                    $admin = Admin::find($admin_id->id);
                    //$admin->notify(new WebPush("Notifications", trans('api.push.incoming_request'), route('admin.dispatcher.index') ));
                }

            }

            //Add the Log File for ride
            $user_request = new UserRequest();
            $user_request->request_id = $newRequest->id;
            $user_request->user_id = $newRequest->user_id;
            $user_request->provider_id = $newRequest->provider_id;
            $user_request->admin_service =$newRequest->admin_service;
            $user_request->schedule_at = $newRequest->schedule_at;
            $user_request->status = $newRequest->status;
            $user_request->request_data = json_encode($newRequest);
            $user_request->company_id = Auth::user()->company_id; 
            $user_request->save();

            if ($request->schedule_date == "" && $request->schedule_time == "") {
                if($newRequest->status != 'SCHEDULED') {
                    if($transportConfig->manual_request == 0){
                        $first_iteration = true;
                        foreach ($Providers as $key => $Provider) {

                            if($transportConfig->broadcast_request == 1){
                               (new SendPushNotification)->IncomingRequest($Provider->id, 'transport'); 
                            }

                            $existingRequest =  RequestFilter::where('provider_id', $Provider->id)->first();
                            if($existingRequest == null) {
                                $Filter = new RequestFilter;
                                // Send push notifications to the first provider
                                // incoming request push to provider
                                $Filter->admin_service = $newRequest->admin_service;
                                $Filter->request_id = $user_request->id;
                                $Filter->provider_id = $Provider->id; 

                                if($transportConfig->broadcast_request == 0 && $first_iteration == false ) {
                                    $Filter->assigned = 1;
                                }

                                $Filter->company_id = Auth::user()->company_id; 
                                $Filter->save();
                            }
                            $first_iteration = false;
                        }
                    }

                    //Send message to socket
                    $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id ];
                    app('redis')->publish('newRequest', json_encode( $requestData ));

                }
            }

            if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
                if( $siteConfig->mail_driver == 'SMTP') {
                    //  SEND OTP TO MAIL
                    $subject='Request|OTP';
                    $templateFile = 'mails/requestotp';
                    $toEmail = $User->email;
                    $data=['body' => $newRequest->otp, 'username' => $User->first_name];
                    //$result= Helper::send_emails($templateFile, $toEmail, $subject, $data);               
                }
            }

            if( !empty($siteConfig->send_sms) && $siteConfig->send_sms == 1) {
                $smsMessage ='Your Otp to start the request is '.$newRequest->otp;
                $plusCodeMobileNumber = $User->mobile;
                // send OTP SMS here            
                //Helper::send_sms($User->company_id, $plusCodeMobileNumber, $smsMessage);
            }

            return Helper::getResponse([ 'data' => [
                        'message' => ($newRequest->status == 'SCHEDULED') ? 'Schedule request created!' : 'New request created!',
                        'request_id' => $newRequest->id,
                        'current_provider' => $newRequest->provider_id,
                    ]]);

        } catch (Exception $e) {  
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function cancel_ride(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:user_requests,request_id',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
        ]);

        try{

            $newRequest = UserRequest::where('admin_service', $request->admin_service)->where('request_id', $request->id)->first();

            if($newRequest->status == 'CANCELLED')
            {
                return Helper::getResponse(['status' => 404, 'message' => trans('api.ride.already_cancelled')]);
            }

            if(in_array($newRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED'])) {

                if($request->admin_service == "TRANSPORT" ) { 

                    try {
                        $rideRequest = \App\Models\Transport\RideRequest::find($request->id);
                        $rideRequest->cancelled_by = 'ADMIN';
                        $rideRequest->status = 'CANCELLED';
                        $rideRequest->save();

                        $setting = Setting::where('company_id', $rideRequest->company_id)->first();
                         
                        //Send message to socket
                        $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
                        app('redis')->publish('newRequest', json_encode( $requestData ));

                    } catch(\Throwable $e) { }
                    
                } else if ($request->admin_service == "ORDER") {
                     try {
                        $storeorder=\App\Models\Order\StoreOrder::find($request->id);
                        $storeorder->cancelled_by = 'ADMIN';
                        $storeorder->status = 'CANCELLED';
                        $storeorder->save();

                        $setting = Setting::where('company_id', $storeorder->company_id)->first();
                   
                        $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $storeorder->city_id : 0, 'user' => $newRequest->user_id ];
                        app('redis')->publish('newRequest', json_encode( $requestData ));

                     } catch(\Throwable $e) { }
                } else if ($request->admin_service == "SERVICE") {
                     try {
                        $serviceRequest = \App\Models\Service\ServiceRequest::find($request->id);
                        $serviceRequest->cancelled_by = 'ADMIN';
                        $serviceRequest->status = 'CANCELLED';
                        $serviceRequest->save();

                        $setting = Setting::where('company_id', $serviceRequest->company_id)->first();
                   
                        $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $serviceRequest->city_id : 0, 'user' => $newRequest->user_id ];
                        app('redis')->publish('newRequest', json_encode( $requestData ));

                     } catch(\Throwable $e) { }
                }

                RequestFilter::where('admin_service', $request->admin_service )->where('request_id', $newRequest->id)->delete();
                UserRequest::where('id',$newRequest->id)->delete();
                $newRequest->delete();
               return Helper::getResponse(['message' => trans('api.ride.ride_cancelled')]);

            } else {
                return Helper::getResponse(['status' => 403, 'message' => trans('api.ride.already_onride')]);
            }
        }

        catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function create_service(Request $request)
    {

        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'country_code' => 'required|numeric',
            'country_id' => 'required',
            'city_id' => 'required',
        ]);

        // return $request->all();

        $country = Country::find($request->country_id);

        $state = State::where('country_id', $country->id)->first();

        $timezone = $state->timezone;

        $currency = $country->country_currency;

        $mobile = $this->cusencrypt($request->mobile,env('DB_SECRET'));
        $email = $this->cusencrypt($request->email,env('DB_SECRET'));

        try {
            $User = User::where('mobile', $mobile)->firstOrFail();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $email)->firstOrFail();
            } catch (Exception $e) {
                $User = User::create([
                    'company_id' => Auth::user()->company_id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'country_code' => $request->country_code,
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->mobile),
                    'country_id' => $request->country_id,
                    'state_id' => $state->id,
                    'city_id' => $request->city_id,
                    'currency' => $currency,
                    'payment_mode' => 'CASH'
                ]);
            }
        }

        $provider_id = $request->provider_id;
        $provider = Provider::find($provider_id);
        $company_id = $User->company_id; 
        $FilterCheck = RequestFilter::where(['admin_service'=>'SERVICE','provider_id'=>$provider_id,'company_id'=>$company_id])->first();
        if($FilterCheck != null) {
            return Helper::getResponse(['status' => 422, 'message' => trans('api.ride.request_inprogress')]);
        }
        $ActiveRequests = ServiceRequest::PendingRequest($User->id)->count();
        if($ActiveRequests > 0) {
            return Helper::getResponse(['status' => 422, 'message' => trans('api.ride.request_inprogress')]);
        }

        $setting = Setting::where('company_id', Auth::user()->company_id)->first();

        $settings = json_decode(json_encode($setting->settings_data));

        $siteConfig = $settings->site;

        $serviceConfig = $settings->service;
        
        $timezone =  ($User->state_id) ? State::find($User->state_id)->timezone : '';
        // $currency =  Country::find(Auth::user()->country_id) ? Country::find(Auth::user()->country_id)->country_currency : '' ;
        $currency = CompanyCountry::where('company_id',$company_id)->where('country_id', $User->country_id)->first();
        if($request->has('schedule_date') && $request->has('schedule_time')){
            $schedule_date = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');
           
            $beforeschedule_time = (new Carbon($schedule_date))->subHour(1);
            $afterschedule_time = (new Carbon($schedule_date))->addHour(1);

            $CheckScheduling = ServiceRequest::where('status','SCHEDULED')
                            ->where('user_id', $User->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->count();


            if($CheckScheduling > 0){
                return Helper::getResponse(['status' => 422, 'message' => trans('api.ride.request_already_scheduled')]);
            }

        }
        $distance = $serviceConfig->provider_search_radius ? $serviceConfig->provider_search_radius : 100;
        // $distance = config('constants.provider_search_radius', '10');

        $latitude =$request->s_latitude;
        $longitude = $request->s_longitude;
        $service_id = $request->service_id;


        $Provider = Provider::with('service','rating')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id','first_name','picture')
            ->where('id', $provider_id)
            ->orderBy('distance','asc')
            ->first();

        try {
            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin='.$request->s_latitude.','.$request->s_longitude.'&destination='.$request->s_latitude.','.$request->s_longitude.'&mode=driving&key='.$siteConfig->server_key;

            $json = Helper::curl($details);

            $details = json_decode($json, TRUE);

            $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

            $serviceRequest = new ServiceRequest;
            $serviceRequest->company_id = $company_id;
            $prefix = $serviceConfig->booking_prefix;
            $serviceRequest->booking_id = Helper::generate_booking_id($prefix);
            $serviceRequest->admin_service =  'SERVICE';
            $serviceRequest->user_id = $User->id;
            
            //$serviceRequest->provider_service_id = $request->service_id;
            $serviceRequest->service_id = $request->service_id;
            $serviceRequest->service_category_id = Service::select('service_category_id')->where('id',$request->service_id)->first()->service_category_id;

            $serviceRequest->provider_id =  $provider_id;
            //$serviceRequest->rental_hours = $request->rental_hours;
            $serviceRequest->payment_mode = 'CASH';
            $serviceRequest->promocode_id = 0;
            
            $serviceRequest->status = 'ACCEPTED';

            $serviceRequest->timezone = $timezone;
            $serviceRequest->currency = ($currency != null) ? $currency->currency : '' ;

            $serviceRequest->city_id = $request->city_id;
            $serviceRequest->country_id = $request->country_id;

            $serviceRequest->s_address = $request->s_address ? $request->s_address : "Address";

            $serviceRequest->s_latitude = $latitude;
            $serviceRequest->s_longitude = $longitude;

            $serviceRequest->track_latitude = $latitude;
            $serviceRequest->track_longitude =  $longitude;

            $serviceRequest->allow_description = $request->allow_description;
            if($request->hasFile('allow_image')) {
                $serviceRequest->allow_image = Helper::upload_file($request->file('allow_image'), 'service/image', null, $company_id);
            }
            // $serviceRequest->quantity = $request->quantity;
            // $serviceRequest->price = $request->price;

            $serviceRequest->distance = $request->distance ? $request->distance  : 0;
            $serviceRequest->unit = config('constants.distance', 'Kms');

            if($User->wallet_balance > 0){
                $serviceRequest->use_wallet = $request->use_wallet ? : 0;
            }

            $serviceRequest->otp = mt_rand(1000 , 9999);

            $serviceRequest->assigned_at = (Carbon::now())->toDateTimeString();
            $serviceRequest->route_key = $route_key;
            $serviceRequest->admin_id = $provider->admin_id;

            /*if($Providers->count() <= config('constants.surge_trigger') && $Providers->count() > 0){
                $serviceRequest->surge = 1;
            }*/

            if($request->has('schedule_date') && $request->has('schedule_time') && trim($request->schedule_date) != '' && trim($request->schedule_time) != ''){
                $serviceRequest->status = 'SCHEDULED';
                $serviceRequest->schedule_at = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');
                $serviceRequest->is_scheduled = 'YES';
            }
            if($serviceRequest->status != 'SCHEDULED') {
                if($serviceConfig->manual_request == 0 && $serviceConfig->broadcast_request == 0) {
                    //Log::info('New Request id : '. $rideRequest->id .' Assigned to provider : '. $rideRequest->provider_id);
                    // (new SendPushNotification)->IncomingRequest($Providers[0]->id, 'service');
                }
            }   
            $serviceRequest->save();
            if($serviceConfig->manual_request == 1) {

                // $admins = Admin::select('id')->get();

                // foreach ($admins as $admin_id) {
                //     $admin = Admin::find($admin_id->id);
                //     //$admin->notify(new WebPush("Notifications", trans('api.push.incoming_request'), route('admin.dispatcher.index') ));
                // }

            }

            $serviceRequest = ServiceRequest::with('service','service.serviceCategory','service.servicesubCategory')->where('id', $serviceRequest->id)->first();

            //Add the Log File for ride
            $serviceRequestId = $serviceRequest->id;
            $user_request = new UserRequest();
            $user_request->request_id = $serviceRequest->id;
            $user_request->user_id = $serviceRequest->user_id;
            $user_request->provider_id = $serviceRequest->provider_id;
            $user_request->schedule_at = $serviceRequest->schedule_at;
            $user_request->company_id = $company_id;
            $user_request->admin_service ='SERVICE';
            $user_request->status = $serviceRequest->status;
            $user_request->request_data = json_encode($serviceRequest);
            $user_request->save();

            if($serviceRequest->status != 'SCHEDULED') {
                if($serviceConfig->manual_request == 0){
                    (new SendPushNotification)->IncomingRequest($Provider->id, 'service');
                    /* if($serviceConfig->broadcast_request == 1){
                       //(new SendPushNotification)->IncomingRequest($Provider->id, 'service'); 
                    }*/
                    $Filter = new RequestFilter;
                    // Send push notifications to the first provider
                    // incoming request push to provider
                    $Filter->admin_service = 'SERVICE';
                    $Filter->request_id = $user_request->id;
                    $Filter->provider_id = $provider_id; 
                    $Filter->company_id = $company_id; 
                    $Filter->save(); 
                }
                
                //Send message to socket
                $requestData = ['type' => 'SERVICE', 'room' => 'room_'.$company_id, 'id' => $serviceRequest->id, 'city' => ($setting->demo_mode == 0) ? $serviceRequest->city_id : 0, 'user' => $serviceRequest->user_id ];
                app('redis')->publish('newRequest', json_encode( $requestData ));

            }

            if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
                if( $siteConfig->mail_driver == 'SMTP') {
                    //  SEND OTP TO MAIL
                    $subject='Request|OTP';
                    $templateFile = 'mails/requestotp';
                    $toEmail = $User->email;
                    $data=['body' => $serviceRequest->otp, 'username' => $User->first_name];
                    $result= Helper::send_emails($templateFile, $toEmail, $subject, $data);               
                }
            }

            if( !empty($siteConfig->send_sms) && $siteConfig->send_sms == 1) {
                $smsMessage ='Your Otp to start the request is '.$serviceRequest->otp;
                $plusCodeMobileNumber = $User->mobile;
                // send OTP SMS here            
                Helper::send_sms($User->company_id, $plusCodeMobileNumber, $smsMessage);
            }

            return Helper::getResponse([ 'data' => [
                'message' => ($serviceRequest->status == 'SCHEDULED') ? 'Schedule request created!' : 'New request created!',
                'request_id' => $serviceRequest->id
            ]]);

        } catch (Exception $e) {  
            return Helper::getResponse(['status' => 500, 'message' => trans('api.service.request_not_completed'), 'error' => $e->getMessage() ]);
        }
    }

    public function cancel_service(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:user_requests,request_id',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
        ]);

        try{

            $newRequest = UserRequest::where('admin_service', 'SERVICE')->where('request_id', $request->id)->first();

            if($newRequest->status == 'CANCELLED')
            {
                return Helper::getResponse(['status' => 404, 'message' => trans('api.ride.already_cancelled')]);
            }

            if(in_array($newRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED'])) {

                $admin_service = AdminService::find($request->service_id);

                if($request->admin_service == "TRANSPORT" ) { 

                    try {
                        $rideRequest = \App\Models\Transport\RideRequest::find($request->id);
                        $rideRequest->cancelled_by = 'ADMIN';
                        $rideRequest->status = 'CANCELLED';
                        $rideRequest->save();

                $setting = Setting::where('company_id', $rideRequest->company_id)->first();
                         
                         //Send message to socket
                $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
                app('redis')->publish('newRequest', json_encode( $requestData ));

                    } catch(\Throwable $e) { }
                    
                } else if ($request->admin_service == "ORDER") {
                     try {
                   $storeorder=\App\Models\Order\StoreOrder::find($request->id);
                   $storeorder->cancelled_by = 'ADMIN';
                   $storeorder->status = 'CANCELLED';
                   $storeorder->save();

                   $setting = Setting::where('company_id', $storeorder->company_id)->first();
                   
                   $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $request->city_id : 0, 'user' => $newRequest->user_id ];
                    app('redis')->publish('newRequest', json_encode( $requestData ));

                     } catch(\Throwable $e) { }
                } else if ($request->admin_service == "SERVICE") {
                     try {
                        $serviceRequest = \App\Models\Service\ServiceRequest::find($request->id);
                        $serviceRequest->cancelled_by = 'ADMIN';
                        $serviceRequest->status = 'CANCELLED';
                        $serviceRequest->save();

                        $setting = Setting::where('company_id', $serviceRequest->company_id)->first();
                   
                        $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::user()->company_id, 'id' => $newRequest->id, 'city' => ($setting->demo_mode == 0) ? $request->city_id : 0, 'user' => $newRequest->user_id ];
                        app('redis')->publish('newRequest', json_encode( $requestData ));

                     } catch(\Throwable $e) { }
                }

                RequestFilter::where('admin_service', 'SERVICE' )->where('request_id', $newRequest->id)->delete();
                UserRequest::where('id',$newRequest->id)->delete();
                $newRequest->delete();
               return Helper::getResponse(['message' => trans('api.ride.ride_cancelled')]);

            } else {
                return Helper::getResponse(['status' => 403, 'message' => trans('api.ride.already_onride')]);
            }
        }

        catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function fare(Request $request){

        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'numeric',
                'provider_service' => 'required',
            ]);

        $settings = json_decode(json_encode(Setting::where('company_id', Auth::user()->company_id)->first()->settings_data));

        $siteConfig = $settings->site;

        $transportConfig = $settings->transport;

        try {
            if($request->admin_service == "TRANSPORT" ) {
                $newRequest = \App\Models\Transport\RideRequest::find($request->id);
            }
        } catch(\Throwable $e) { }

        try{       
            $response = new ServiceTypes();
            $request->request->add(['server_key' => $siteConfig->server_key]);
            $request->request->add(['service_type' => $request->provider_service]);
            $request->request->add(['city_id' => $request->city]);
            $request->request->add(['company_id' => Auth::user()->company_id]);
            $responsedata=$response->calculateFare($request->all(),1);

            if(!empty($responsedata['errors'])){
                throw new Exception($responsedata['errors']);
            }
            else{
                return response()->json($responsedata['data']);
            }

        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function providerServiceList(Request $request)
    {
        $settings = json_decode(json_encode(Setting::where('company_id', Auth::user()->company_id)->first()->settings_data));

        $siteConfig = $settings->site;
        $serviceConfig = $settings->service;

        $distance = $serviceConfig->provider_search_radius ? $serviceConfig->provider_search_radius : 100;
       
        $latitude = $request->lat;
        $longitude = $request->long;
        $service_id = $request->id;
        
        //$timezone =  (Auth::user()->state_id) ? State::find(Auth::user()->state_id)->timezone : '';
        // $currency =  Country::find(Auth::user()->country_id) ? Country::find(Auth::user()->country_id)->country_currency : '' ;

        $currency = CompanyCountry::where('company_id',Auth::user()->company_id)->where('country_id',Auth::user()->country_id)->first();
        $service_cancel_provider = ServiceCancelProvider::select('id','provider_id')->where('company_id',Auth::user()->company_id)->where('user_id',Auth::user()->id)->pluck('provider_id','provider_id')->toArray();

        $callback = function ($q) use ($service_id) {
            $q->where('admin_service','SERVICE');
            $q->where('service_id',$service_id);
        };

  
        $provider_service_init = Provider::with(['service'=> $callback,'service_city','request_filter'])
        ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id','first_name','picture','rating','city_id','latitude','longitude')
        ->where('status', 'approved')
        ->where('is_online',1)->where('is_assigned',0)
        ->where('company_id', Auth::user()->company_id)
        ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
        ->whereDoesntHave('request_filter')
        ->whereHas('service', function($q) use ($service_id){          
            $q->where('admin_service','SERVICE');
            $q->where('service_id',$service_id);
        });
        if($request->has('name')){
            $provider_service_init->where('first_name','LIKE', '%' . $request->name . '%');
            //$provider_service_init->orderBy('first_name','asc');
        }
        $provider_service_init->orderBy('distance','asc');
        

        $provider_service_init->whereNotIn('id',$service_cancel_provider);
        $provider_service = $provider_service_init->get();

        if($provider_service){
            $providers = [];
            if(!empty($provider_service[0]->service)){
                $serviceDetails=Service::with('serviceCategory')->where('id',$service_id)->where('company_id',Auth::user()->company_id)->first();
                foreach($provider_service as $key=> $service){ 
                    unset($service->request_filter);
                    $provider = new \stdClass();
                    $provider->distance=$service->distance;
                    $provider->id=$service->id;
                    $provider->first_name=$service->first_name;
                    $provider->picture=$service->picture;
                    $provider->rating=$service->rating;
                    $provider->city_id=$service->city_id;
                    $provider->latitude=$service->latitude;
                    $provider->longitude=$service->longitude;
                    if($service->service_city==null){
                        $provider->fare_type='FIXED';
                        $provider->base_fare='0';
                        $provider->per_miles='0';
                        $provider->per_mins='0';
                        $provider->price_choose='';
                    }
                    else{
                        $provider->fare_type=$service->service_city->fare_type;
                        if($serviceDetails->serviceCategory->price_choose=='admin_price'){
                           if(!empty($request->qty))
                               $provider->base_fare=Helper::decimalRoundOff($service->service_city->base_fare*$request->qty);
                           else
                               $provider->base_fare=Helper::decimalRoundOff($service->service_city->base_fare);

                           $provider->per_miles=Helper::decimalRoundOff($service->service_city->per_miles);
                           $provider->per_mins=Helper::decimalRoundOff($service->service_city->per_mins*60);
                       }
                       else{
                           if(!empty($request->qty))
                               $provider->base_fare=Helper::decimalRoundOff($service->service->base_fare*$request->qty);
                           else
                               $provider->base_fare=Helper::decimalRoundOff($service->service->base_fare);

                           $provider->per_miles=Helper::decimalRoundOff($service->service->per_miles);
                           $provider->per_mins=Helper::decimalRoundOff($service->service->per_mins*60);
                       }

                        $provider->price_choose=$serviceDetails->serviceCategory->price_choose;
                    }    

                    $providers[] = $provider;
                }

            }

            return Helper::getResponse(['data' =>['provider_service' => $providers,'currency' => ($currency != null) ? $currency->currency: '']]);

        }
    }

}
