<?php 

namespace App\Services\V1\Common;

use Illuminate\Http\Request;
use Validator;
use Exception;
use DateTime;
use Carbon\Carbon;
use Auth;
use Lang;
use Log;
use App\Helpers\Helper;
use GuzzleHttp\Client;
use App\Models\Common\Rating;
use App\Models\Common\Reason;
use App\Models\Common\AdminService;
use App\Models\Common\RequestFilter;
use App\Models\Transport\RideCityPrice;
use App\Models\Common\PeakHour;
use App\Models\Common\AdminWallet;
use App\Models\Common\User;
use App\Models\Common\UserWallet;
use App\Models\Common\FleetWallet;
use App\Models\Common\Provider;
use App\Models\Common\ProviderWallet;
use App\Models\Common\Admin;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Common\Setting;
use App\Models\Common\CountryBankForm;
use App\Models\Common\ProviderCard;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\ProviderService;
use App\Models\Common\ProviderBankdetail;
use App\Models\Common\ProviderDocument;
use App\Models\Common\Document;
use App\Models\Common\UserRequest;
use App\Models\Service\ServiceRequest;
use App\Services\SendPushNotification; 
use App\Models\Common\CompanyCountry;
use App\Models\Common\Notifications;
use App\Models\Common\CompanyCity;
use App\Models\Service\Service;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreOrderDispute;
use App\Services\ReferralResource;
use App\Models\Common\Chat;
use App\Traits\Actions;
use DB;


class ProviderServices { 

	use Actions;

	public function checkRequest($request) {

		try{

			$provider = Provider::where('id', $this->user->id)->first();
			$settings = Helper::setting();
			$siteConfig = $settings->site;  
			 
			$IncomingRequests = RequestFilter::with(['request.user', 'request.service', 'request.service', 'request'])
			->whereHas('request', function($query) {
				$query->where('status','<>', 'CANCELLED');
				$query->where('status','<>', 'SCHEDULED');
				$query->where('status','<>', 'PROVIDEREJECTED');
			})
			->where('provider_id', $provider->id)
			->whereIn('admin_service', ['TRANSPORT', 'ORDER', 'SERVICE'])
			->where('assigned', '0')
			->get();

			if(!empty($request->latitude)) {
				$provider->update([
						'latitude' => $request->latitude,
						'longitude' => $request->longitude,
				]);

			//when the provider is idle for a long time in the mobile app, it will change its status to hold. If it is waked up while new incoming request, here the status will change to active
			//DB::table('provider_services')->where('provider_id',$provider->id)->where('status','hold')->update(['status' =>'active']);
			}
			$is_otp = 0;
			$config ='';

			if(!empty($IncomingRequests)){

				for ($i=0; $i < count($IncomingRequests); $i++) {

					$admin_service=$IncomingRequests[$i]->admin_service;

					//Transport
					if($admin_service == "TRANSPORT") {

						$config = $settings->transport;
						$is_otp = $config->ride_otp;
					}

					//Service
					elseif($admin_service == "SERVICE"){

						$config = $settings->service;
						$is_otp = $config->serve_otp;

						$serviceRequestid = $IncomingRequests[$i]->request->request_id;
						$serveRequest = ServiceRequest::select('id','service_id')->where('id',$serviceRequestid)->first();
						if($serveRequest != null){
							$IncomingRequests[$i]->request->service_details = Service::where('id',$serveRequest->service_id)->first();
						}else{
							$IncomingRequests[$i]->request->service_details = null;
						}
					}

					//Order
					elseif($admin_service == "ORDER"){

						$config = $settings->order;
						$is_otp = $config->serve_otp;

						$orderRequestid = $IncomingRequests[$i]->request_id;
						$IncomingRequests[$i]->request->store_orders = $orderRequest = StoreOrder::select('id','store_id','delivery_address','pickup_address')
						->with(['storesDetails' => function($query){  $query->select('id', 'store_name','store_location' ); }
						])->where('id',$orderRequestid)->first();

					}

					$this->assignProvider($config, $IncomingRequests[$i]);
					
				}
			}

			$Reason=Reason::select('id', 'reason')->where('type','PROVIDER')->get();

			$referral = (new ReferralResource)->get_referral('provider', $this->user->id)[0];

			$Response = [ 
					'account_status' => $provider->status,
					'service_status' => (count($IncomingRequests) > 0) ? $admin_service : 'ACTIVE',
					'requests' => (count($IncomingRequests) > 0) ? [$IncomingRequests[0]->request]: [],
					'provider_details' => $provider,
					'reasons' => $Reason,
					'referral_count' => $siteConfig->referral_count,
					'referral_amount' => $siteConfig->referral_amount,
					'ride_otp' => $is_otp,
					'referral_total_count' => $referral->total_count,
					'referral_total_amount' => $referral->total_amount,
				];

			return $Response;
		} catch (ModelNotFoundException $e) {
			return $e->getMessage();
		}
	}

	public function acceptRequest(Request $request) {

		try {        

			$provider_vehicle = null;

			try {
				if($request->admin_service == "TRANSPORT" ) {

					$validator = Validator::make($request->all(), [
						'id' => 'required|numeric|exists:transport.ride_requests,id',]);

					if ($validator->fails()) {

						$errors = [];
						foreach (json_decode( $validator->errors(), true ) as $key => $error) {
						   $errors[] = $error[0];
						}

						header("Access-Control-Allow-Origin: *");
						header("Access-Control-Allow-Headers: *");
						header('Content-Type: application/json');
						http_response_code(422);
						echo json_encode(Helper::getResponse(['status' => 422, 'message' => !empty($errors[0]) ? $errors[0] : "",  'error' => !empty($errors[0]) ? $errors[0] : "" ])->original);
						exit;
					}

					$newRequest = \App\Models\Transport\RideRequest::with('user')->find($request->id);

					$provider_vehicle = ProviderVehicle::where('provider_id', $this->user->id)->where('vehicle_service_id', $newRequest->ride_delivery_id)->first();

					if($this->user->admin_id != null) {
						$newRequest->admin_id = $this->user->admin_id;
					}

					if($newRequest->status != "SEARCHING") {
						return ['status' => 422, 'message' => trans('api.ride.request_inprogress') ];
					}
				}
			} catch(\Throwable $e) { }  

			try {
				if($request->admin_service == "SERVICE" ) {

					$validator = Validator::make($request->all(), [
						'id' => 'required|numeric|exists:service.service_requests,id',]);

					if ($validator->fails()) {

						$errors = [];
						foreach (json_decode( $validator->errors(), true ) as $key => $error) {
						   $errors[] = $error[0];
						}

						header("Access-Control-Allow-Origin: *");
						header("Access-Control-Allow-Headers: *");
						header('Content-Type: application/json');
						http_response_code(422);
						echo json_encode(Helper::getResponse(['status' => 422, 'message' => !empty($errors[0]) ? $errors[0] : "",  'error' => !empty($errors[0]) ? $errors[0] : "" ])->original);
						exit;
					}

					$newRequest = \App\Models\Service\ServiceRequest::with('user')->find($request->id);
					if($newRequest->status != "SEARCHING") {
						return ['status' => 422, 'message' => trans('api.service.request_inprogress') ];
					}
				}
			} catch(\Throwable $e) { }

			try {
				if($request->admin_service == "ORDER" ) {

					$validator = Validator::make($request->all(), [
						'id' => 'required|numeric|exists:order.store_orders,id',]);

					if ($validator->fails()) {

						$errors = [];
						foreach (json_decode( $validator->errors(), true ) as $key => $error) {
						   $errors[] = $error[0];
						}

						header("Access-Control-Allow-Origin: *");
						header("Access-Control-Allow-Headers: *");
						header('Content-Type: application/json');
						http_response_code(422);
						echo json_encode(Helper::getResponse(['status' => 422, 'message' => !empty($errors[0]) ? $errors[0] : "",  'error' => !empty($errors[0]) ? $errors[0] : "" ])->original);
						exit;
					}

					try{
						$newRequest = \App\Models\Order\StoreOrder::with('user')->find($request->id);
					}catch(Exception $e){
						return ['status' => 500, 'message' => trans('api.order.order_not_found')] ;
					}
					if($newRequest->status != "SEARCHING") {
						return ['status' => 422, 'message' => trans('api.order.request_inprogress') ];
					}
				}
			} catch(\Throwable $e) {
				return ['status' => 500, 'message' => trans('api.order.order_not_found'), 'error' => $e->getMessage() ];
			}



			$user_request = UserRequest::where('request_id', $newRequest->id)->where('admin_service', $request->admin_service)->first();        
			
			$newRequest->provider_id = $this->user->id;
			if($request->admin_service == "TRANSPORT") {
				$newRequest->provider_vehicle_id = $provider_vehicle->id;
			}



			if($newRequest->schedule_at != ""){

				$beforeschedule_time = strtotime($newRequest->schedule_at."- 1 hour");
				$afterschedule_time = strtotime($newRequest->schedule_at."+ 1 hour");


				try {

					if($request->admin_service == "TRANSPORT" ) {
						$CheckScheduling = \App\Models\Transport\RideRequest::where('status','SCHEDULED')
							->where('provider_id', $this->user->id)
							->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
							->count();

						if($CheckScheduling > 0 ){
							return ['status' => 403, 'message' => trans('api.ride.request_already_scheduled') ];
						}
					}

				} catch (\Throwable $e) { }

				try {

					if($request->admin_service == "ORDER" ) {
						$CheckScheduling = \App\Models\Order\StoreOrder::where('status','SCHEDULED')
							->where('provider_id', $this->user->id)
							->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
							->count();

						if($CheckScheduling > 0 ){
							return ['status' => 403, 'message' => trans('api.order.request_already_scheduled') ];
						}
					}

				} catch (\Throwable $e) { }

				try {

					if($request->admin_service == "SERVICE" ) {
						$CheckScheduling = \App\Models\Service\ServiceRequest::where('status','SCHEDULED')
							->where('provider_id', $this->user->id)
							->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
							->count();

						if($CheckScheduling > 0 ){
							return ['status' => 403, 'message' => trans('api.service.request_already_scheduled') ];
						}
					}

				} catch (\Throwable $e) { }

				$newRequest->status = "SCHEDULED";
				$newRequest->save();

				$user_request->provider_id = $newRequest->provider_id;
				$user_request->status = $newRequest->status;
				$user_request->request_data = json_encode($newRequest);

			}else{

				if($request->admin_service == "SERVICE" ) {
					$newRequest->status = "ACCEPTED";        
					$newRequest->save();    
					//Send message to socket
					$requestData = ['type' => 'SERVICE', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => $newRequest->city_id ];
				
				}else if($request->admin_service == "ORDER" ) {
					$newRequest->status = "PROCESSING";    
					$newRequest->save();
					//Send message to socket
					$requestData = ['type' => 'ORDER', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => $newRequest->city_id ];
								
				}else{
					$newRequest->status = "STARTED";
					$newRequest->save();    
					//Send message to socket
					$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id, 'message' => 'testing' ];
				}                

				$user_request->provider_id = $newRequest->provider_id;
				$user_request->status = $newRequest->status;
				$user_request->request_data = json_encode($newRequest);

				$publishUrl = 'newRequest';
				if($newRequest->admin_service == "TRANSPORT") $publishUrl = 'checkTransportRequest';
				if($newRequest->admin_service == "ORDER") $publishUrl = 'checkOrderRequest';
				if($newRequest->admin_service == "SERVICE") $publishUrl = 'checkServiceRequest';

				app('redis')->publish($publishUrl, json_encode( $requestData ));
				
			}

			$user_request->save();
			$provider = Provider::find($this->user->id);
			$provider->is_assigned = 1;
			$provider->save();

			$Filters = RequestFilter::select('id')->where('admin_service', $user_request->admin_service)->where('request_id', $user_request->id)->where('provider_id', '!=', $this->user->id)->get();

			$user_request_last = UserRequest::where('status', 'SEARCHING')->where('admin_service', $request->admin_service)->orderby('id', 'desc')->first();

			if($user_request_last != null) {
				DB::table('request_filters')->whereIn('id', $Filters)->update(['request_id' => $user_request_last->id]);

				app('redis')->publish('newRequest', json_encode( $requestData ));
			} else {
				foreach ($Filters as $Filter) {
					$Filter->delete();
				}
			}


			// Send Push Notification to User
			
			if($request->admin_service == "TRANSPORT" ) {
				(new SendPushNotification)->RideAccepted($newRequest, strtolower($request->admin_service), trans('api.ride.request_accepted'));
				return ['status' => 200, 'message' => trans('api.ride.request_accepted'), 'data' => $newRequest  ];
			} else if($request->admin_service == "ORDER" ) {
				(new SendPushNotification)->RideAccepted($newRequest, strtolower($request->admin_service), trans('api.order.request_accepted'));
				return ['status' => 200, 'message' => trans('api.order.request_accepted'), 'data' => $newRequest  ];
			} else if($request->admin_service == "SERVICE" ) {
				(new SendPushNotification)->RideAccepted($newRequest, strtolower($request->admin_service), trans('api.service.request_accepted'));
				return ['status' => 200, 'message' => trans('api.service.request_accepted'), 'data' => $newRequest  ];
			}
  
		} catch (Exception $e) {
			throw new Exception($e->getMessage()) ;
		}
	}

	public function cancelRequest(Request $request) {

		$UserRequest = UserRequest::where('admin_service' , $request->admin_service)->where('request_id', $request->id)->first();

		if($UserRequest == null) {
			if($request->admin_service == "TRANSPORT" ) {
				return Helper::getResponse(['message' => trans('api.ride.request_rejected') ]);
			} else if($request->admin_service == "ORDER" ) {
				return Helper::getResponse(['message' => trans('api.order.request_rejected') ]);
			} else if($request->admin_service == "SERVICE" ) {
				return Helper::getResponse(['message' => trans('api.service.request_rejected') ]);
			}
			
		}

		$request_filter = RequestFilter::select('id')->where('request_id', $UserRequest->id)->where('admin_service', $request->admin_service)->where('provider_id', $this->user->id)->first();

		try {

			if($request_filter != null) {
				$request_filter->delete();
			} else {
				if($request->admin_service == "TRANSPORT" ) {
					return trans('api.ride.request_rejected') ;
				} else if($request->admin_service == "ORDER" ) {
					return trans('api.order.request_rejected') ;
				} else if($request->admin_service == "SERVICE" ) {
					return trans('api.service.request_rejected') ;
				}
				
			}

			try {


				if($request->admin_service == "TRANSPORT" ) {
					$config = $this->settings->transport;
				}


				if($request->admin_service == "SERVICE" ) {

					$config = $this->settings->service;

					$serviceRequest = ServiceRequest::findOrFail($request->id);
					$cancelreason = isset($request->reason)?$request->reason:'cancelled';
					ServiceRequest::where('id', $serviceRequest->id)->update(['status' => 'CANCELLED','cancelled_by'=>'PROVIDER','cancel_reason'=>$cancelreason]);
					//ProviderService::where('provider_id',$serviceRequest->provider_id)->update(['status' => 'active']);
					Provider::where('id', $serviceRequest->provider_id)->update(['is_assigned' => 0]);
				}
				
				if($request->admin_service == "ORDER" ) {
					$config = $this->settings->order;
					$this->cancel($request->id, $request->admin_service);
				}else{
					if($config->broadcast_request != 1){
						$this->assignNextProvider($request->id, $request->admin_service);
					}

					$user = User::find($UserRequest->user_id);
					$request_filter = RequestFilter::select('id')->where('request_id', $UserRequest->id)->where('admin_service', $request->admin_service)->count();

					if($request_filter == 0) {
						$this->cancel($request->id, $request->admin_service);
						$UserRequest->delete();

					}
				}

			} catch(\Throwable $e) {
				throw new Exception($e->getMessage()) ;
			} 
			if($request->admin_service == "TRANSPORT" ) {
				return trans('api.ride.request_rejected') ;
			} else if($request->admin_service == "ORDER" ) {
				return trans('api.order.request_rejected') ;
			} else if($request->admin_service == "SERVICE" ) {
				return trans('api.service.request_rejected') ;
			} 
		} catch(\Throwable $e) {
			throw new Exception($e->getMessage()) ;
		}
	}

	public function rate(Request $request, $newRequest ) {
		try {

			 $ratingRequest = Rating::where('request_id', $newRequest->id)->where('admin_service', $newRequest->admin_service )->first();

			 if($ratingRequest == null) {
					 Rating::create([
						 'company_id' => $this->company_id,
						 'admin_service' => $newRequest->admin_service,
						 'provider_id' => $newRequest->provider_id,
						 'user_id' => $newRequest->user_id,
						 'request_id' => $newRequest->id,
                    	 'store_id' => $newRequest->store_id,
						 'provider_rating' => $request->rating,
						 'provider_comment' => $request->comment]);
			 } else {
					 $newRequest->rating->update([
						 'provider_rating' => $request->rating,
						 'provider_comment' => $request->comment,
                    	 'store_rating' => $request->shoprating,
					 ]);
			 }

			 $newRequest->update(['provider_rated' => 1]);

			 $user_request = UserRequest::where('request_id', $request->id)->where('admin_service', $newRequest->admin_service )->first();

			 if($user_request) {
				RequestFilter::where('request_id', $user_request->id)->delete();
				$user_request->delete();
			 }
			 

			 $provider = Provider::find($newRequest->provider_id);
			 $provider->is_assigned = 0;
			 $provider->save();

			 // Send Push Notification to Provider 
			 $average = Rating::where('user_id', $newRequest->user_id)->avg('provider_rating');

			 $user = User::find($newRequest->user_id);
			 $user->rating = $average;
			 $user->save();

			 //$newRequest->user->update(['rating' => $average]);
			 // (new SendPushNotification)->Rate($newRequest, strtolower($newRequest->admin_service));

			 //Send message to socket
			 $requestData = ['type' => $newRequest->admin_service, 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id ];

			 $publishUrl = 'newRequest';
			 if($newRequest->admin_service == "TRANSPORT") $publishUrl = 'checkTransportRequest';
			 if($newRequest->admin_service == "ORDER") $publishUrl = 'checkOrderRequest';
			 if($newRequest->admin_service == "SERVICE") $publishUrl = 'checkServiceRequest';
			 app('redis')->publish($publishUrl, json_encode( $requestData ));

			return ['message' => trans('api.ride.request_completed') ];

		} catch (ModelNotFoundException $e) {
			return ['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' =>trans('api.ride.request_not_completed') ];
		}
	}


	public function assignProvider($config, $IncomingRequest) {
		if($config !='' && $config->manual_request == 0){
			$Timeout = $config->provider_select_timeout;
				$IncomingRequest->request->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequest->request->request->assigned_at));
			
			if($IncomingRequest->request->status == 'SEARCHING' && $IncomingRequest->request->time_left_to_respond < 0) {
				if($config->broadcast_request == 1){
					$this->assignDestroy($IncomingRequest->request->request->id, $IncomingRequest->request->admin_service);
				}else{
					$this->assignNextProvider($IncomingRequest->request->request->id, $IncomingRequest->request->admin_service);
				}
			}
		}
	}

	public function assignDestroy($id, $admin_service)
	{
		
        if($admin_service == "ORDER") {

        	$UserRequest = UserRequest::where('admin_service' , $admin_service)->where('request_id', $id)->first();
        	if($UserRequest!=''){
	        	$request_filter = RequestFilter::where('request_id', $UserRequest->id)->where('admin_service', $admin_service)->delete();
	        	$this->cancel($id, $admin_service);
	        	//$UserRequest->update(['status'=> 'PROVIDEREJECTED']);
        	}
        }else{
        	$this->cancel($id, $admin_service);
			$UserRequest = UserRequest::where('admin_service' , $admin_service)->where('request_id', $id)->delete();
        
		}
		$userRequestLast = UserRequest::where('status', 'SEARCHING')->where('admin_service', $admin_service)->orderby('id')->first();

		if($userRequestLast != null) {

			if($admin_service == "TRANSPORT") {
				try {
					\App\Models\Transport\RideRequest::where('id', $id)->update(['status' => 'CANCELLED']);
					$newRequest =  \App\Models\Transport\RideRequest::findOrFail($userRequestLast->request_id);
				} catch (\Throwable $e) {}

				$distance = isset($this->settings->transport->provider_search_radius) ? $this->settings->transport->provider_search_radius : 100;

				$callback = function ($q) use($newRequest) {
					$q->where('admin_service', $newRequest->admin_service);
					$q->where('ride_delivery_id',$newRequest->ride_delivery_id);
				};

				$withCallback = ['service' => $callback, 'service.ride_vehicle'];
				$whereHasCallback = ['service' => $callback];

				$provider_request = new Request([
					'latitude' => $this->user->latitude,
					'longitude' => $this->user->longitude
				]);

				$Providers = (new UserServices())->availableProviders($provider_request, $withCallback, $whereHasCallback);

				if(count($Providers) > 0) {
					foreach ($Providers as $key => $Provider) {

						$Filter = new RequestFilter;
						$Filter->admin_service = $userRequestLast->admin_service;
						$Filter->request_id = $userRequestLast->id;
						$Filter->provider_id = $Provider->id;
						$Filter->company_id = $this->user->company_id; 
						$Filter->save();

						if($this->settings->transport->broadcast_request == 1){
						   (new SendPushNotification)->IncomingRequest($Provider->id, 'transport_incoming_request', 'Transport Incoming Request'); 
						}

					}

					//Send message to socket
			 		$requestData = ['type' => $admin_service, 'room' => 'room_'.$this->company_id, 'id' => $id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $userRequestLast->user_id ];
					app('redis')->publish('newRequest', json_encode( $requestData ));
				}
			}
		}	
	}

	public function cancel($id, $admin_service) {
		if($admin_service == "TRANSPORT") {
			try {
				$newRequest =  \App\Models\Transport\RideRequest::findOrFail($id);
				$newRequest->status = 'CANCELLED';
				$newRequest->save();

				$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0 ];
				app('redis')->publish('newRequest', json_encode( $requestData ));

			} catch (\Throwable $e) {}

		} else if($admin_service == "ORDER") {
			try {
				$newRequest =  \App\Models\Order\StoreOrder::findOrFail($id);
				\Log::info('cancel provider request'.$id);

				if($admin_service == "ORDER" ) {

					$config = $this->settings->order;
					$userRequest = UserRequest::where('request_id' , $id)->first();
					//dd($userRequest);
					$other_request_filter= RequestFilter::where('request_id' , $userRequest->id)
					->where('admin_service' ,$admin_service)->get();
					//dd($other_request_filter);
					if(count($other_request_filter) <= 0){ 
						\Log::info('cancel provider request'.count($other_request_filter));
						$OrderDetails = $newRequest;
						$storedisputedata=StoreOrderDispute::where('store_order_id',$id)->where('dispute_name','Provider Not Available')->where('status','open')->get();
						if(count($storedisputedata) ==0){
							$storedispute =  new StoreOrderDispute;
							$storedispute->dispute_type='system';
							$storedispute->user_id= $newRequest->user_id;
							$storedispute->store_id= $OrderDetails->store_id;
							$storedispute->store_order_id= $OrderDetails->id;
							$storedispute->dispute_name="Provider Not Available";
							$storedispute->dispute_type_comments="Provider Not Available";
							$storedispute->status="open";
							$storedispute->company_id=$newRequest->company_id;
							$storedispute->save();

							$newRequest->status = 'PROVIDEREJECTED';
							$newRequest->save();
							$userRequest->status = 'PROVIDEREJECTED';
							$userRequest->save();
						}
					}
				
				}
				
				/*$newRequest->status = 'CANCELLED';
				$newRequest->save();*/

				$requestData = ['type' => 'ORDER', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0 ];
				app('redis')->publish('newRequest', json_encode( $requestData ));

			} catch (\Throwable $e) {}

		} else if($admin_service == "SERVICE") {
			try {
				$newRequest =  \App\Models\Service\ServiceRequest::findOrFail($id);
				$newRequest->status = 'CANCELLED';
				$newRequest->save();

				$requestData = ['type' => 'SERVICE', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0 ];
				app('redis')->publish('newRequest', json_encode( $requestData ));

			} catch (\Throwable $e) {}

		}
	}

	public function assignNextProvider($request_id, $admin_service) 
	{

		$UserRequest = UserRequest::where('admin_service' , $admin_service)->where('request_id', $request_id)->first();

		if($admin_service == "TRANSPORT" ) {
			try {
				$newRequest = \App\Models\Transport\RideRequest::with('user')->find($UserRequest->request_id);

				$setting = Setting::where('company_id', $newRequest->company_id)->first();

				$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0 ];

			} catch(\Throwable $e) { }
		} else if($admin_service == "ORDER" ) {
			try {
				$newRequest = \App\Models\Order\StoreOrder::with('user')->find($UserRequest->request_id);

				$requestData = ['type' => 'ORDER', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0 ];

			} catch(\Throwable $e) { }
		} else if($admin_service == "SERVICE" ) {
			try {
				$newRequest = \App\Models\Service\ServiceRequest::with('user')->find($UserRequest->request_id);

				$requestData = ['type' => 'SERVICE', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'user' => $newRequest->user_id, 'city' => ($setting->demo_mode == 0) ? $newRequest->city_id : 0 ];

			} catch(\Throwable $e) { }
		}

		$RequestFilter = RequestFilter::where('admin_service' , $admin_service)->where('request_id', $UserRequest->id)->where('assigned', 0)->orderBy('id')->first();

		if($RequestFilter != null) {
			$RequestFilter->delete();
		}

		try {

			$nextRequestFilter = RequestFilter::where('admin_service' , $admin_service)->where('request_id', $UserRequest->id)->orderBy('id')->first();
			
			if($nextRequestFilter != null) {
				$nextRequestFilter->assigned = 0;
				$nextRequestFilter->save();
				
				$newRequest->assigned_at = (Carbon::now())->toDateTimeString();
				$newRequest->save();


				$UserRequest->request_data = json_encode($newRequest);
				$UserRequest->save();

				// incoming request push to provider
				(new SendPushNotification)->IncomingRequest($nextRequestFilter->provider_id, strtolower($admin_service));
			} else {
				$UserRequest->delete();

				$newRequest->status = 'CANCELLED';
				$newRequest->save();
			}

			if(isset($requestData)) app('redis')->publish('newRequest', json_encode( $requestData ));
			
		} catch (ModelNotFoundException $e) {

			if($admin_service == "TRANSPORT" ) {
				try {
					\App\Models\Transport\RideRequest::where('id', $newRequest->id)->update(['status' => 'CANCELLED']);
				} catch(\Throwable $e) { }
			} else if($admin_service == "ORDER" ) {
				try {
					\App\Models\Order\StoreOrder::where('id', $newRequest->id)->update(['status' => 'CANCELLED']);
				} catch(\Throwable $e) { }
			} else if($admin_service == "SERVICE" ) {
				try {
					\App\Models\Service\ServiceRequest::where('id', $newRequest->id)->update(['status' => 'CANCELLED']);
				} catch(\Throwable $e) { }
			}

			// No longer need request specific rows from RequestMeta
			$RequestFilter = RequestFilter::where('request_id', $UserRequest->id)->orderBy('id')->first();

			if($RequestFilter != null) {
				$RequestFilter->delete();
			}


			if(isset($requestData)) app('redis')->publish('newRequest', json_encode( $requestData ));

			//  request push to user provider not available
			(new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id, strtolower($admin_service));
		}
	}

	public function payment(Request $request, $newRequest ) 
	{
	}


	public function providerHistory(Request $request, $UserRequest, $callback) 
	{
		
		try{

			$type = isset($request->type) ? $request->type : 'past';

			$historyStatus = array('COMPLETED','CANCELLED');
			if ($type == 'upcoming') {
				$historyStatus = array('ACCEPTED', 'STARTED', 'SCHEDULED');
			}
			\Log::info($historyStatus);
			$UserRequest->with($callback)->HistoryProvider(Auth::guard('provider')->user()->id,$historyStatus);

			if($request->has('search_text') && $request->search_text != null) {
				$UserRequest->ProviderhistorySearch($request->search_text);
			}  
			if($request->has('order_by')) {
				$UserRequest->orderby($request->order_by, $request->order_direction);
			}
			if($request->has('limit')) {
				$data=$UserRequest->where('company_id',$this->company_id)->take($request->limit)->offset($request->offset)->get(); 
			} else {
				$data=$UserRequest->where('company_id',$this->company_id)->orderby('id',"desc")->paginate(10); 	
			}


			if(!empty($data)){
				
				$map_icon_start = '';
				//asset('asset/img/marker-start.png');
				$map_icon_end = '';
				//asset('asset/img/marker-end.png');
				foreach ($data as $key => $value) {
					if($request->admin_service == "Order"){
						$value->s_latitude=$value->pickup->latitude;
						$value->s_longitude=$value->pickup->longitude;
						$value->d_latitude=$value->delivery->latitude;
						$value->d_longitude=$value->delivery->longitude;
					}

					$data[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
					"autoscale=1".
					"&size=600x300".
					"&maptype=terrian".
					"&format=png".
					"&visual_refresh=true".
					"&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
					"&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
					"&path=color:0x000000|weight:3|enc:".$value->route_key.
					"&key=".$this->settings->site->server_key;
				}
			}
			return $data;
		}
		catch (Exception $e) {
			
			return response()->json(['error' => $e->getMessage()]);
		}
	}

      public function providerTripsDetails(Request $request, $UserRequest) 
	{
		try{
        	$data=$UserRequest->where('id',$request->id)->where('company_id',$this->company_id)->orderBy('created_at','desc')->first();
        	if(!empty($data)){
				$map_icon_start = '';
				//asset('asset/img/marker-start.png');
				$map_icon_end = '';
				$data->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
						"autoscale=1".
						"&size=320x130".
						"&maptype=terrian".
						"&format=png".
						"&visual_refresh=true".
						"&markers=icon:".$map_icon_start."%7C".$data->s_latitude.",".$data->s_longitude.
						"&markers=icon:".$map_icon_end."%7C".$data->d_latitude.",".$data->d_longitude.
						"&path=color:0x191919|weight:3|enc:".$data->route_key.
						"&key=".$this->settings->site->server_key;
			}
            return $data;		

        }
		catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()]);
		}

    }


       public function providerDisputeCreate(Request $request, $disputeRequest) 
	{
		try{
                $disputeRequest->company_id = $this->company_id;
                if($request->admin_service=="ORDER"){  
                   $disputeRequest->store_order_id = $request->id;
                 } else if($request->admin_service=="Transport"){
                   $disputeRequest->ride_request_id = $request->id;
                 }else if($request->admin_service=="SERVICE"){
                 	$disputeRequest->service_request_id = $request->id;
                 }
                 if($request->has('store_id')){
                  $disputeRequest->store_id =$request->store_id;	
                 }
               
                $disputeRequest->dispute_type =$request->dispute_type;
                $disputeRequest->user_id = $request->user_id;
                $disputeRequest->provider_id = $request->provider_id;                  
                $disputeRequest->dispute_name = $request->dispute_name;
                $disputeRequest->dispute_title ="Provider Dispute"; 
                $disputeRequest->comments =  $request->comments; 

                $disputeRequest->save();
        	    return $disputeRequest;		

        }
		catch (Exception $e) {
			dd($e);
			return response()->json(['error' => $e->getMessage()]);
		}

    }

}
