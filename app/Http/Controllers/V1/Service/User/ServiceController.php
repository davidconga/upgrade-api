<?php

namespace App\Http\Controllers\V1\Service\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\SendPushNotification;
use App\Models\Common\Provider;
use App\Models\Common\RequestFilter;
use App\Models\Common\ProviderService;
use App\Models\Common\Promocode;
use App\Models\Common\Rating;
use App\Helpers\Helper;
use App\Models\Service\ServiceCityPrice;
use App\Models\Service\Service;
use App\Services\V1\Service\Services;
use App\Models\Service\ServiceCategory;
use App\Models\Service\ServiceSubcategory;
use App\Models\Service\ServiceCancelProvider;
use App\Models\Service\ServiceRequestDispute;
use App\Models\Service\ServiceRequestPayment;
use App\Models\Service\ServiceRequest;
use App\Models\Common\Setting;
use App\Models\Common\Country;
use App\Models\Common\CompanyCountry;
use App\Models\Common\State;
use App\Models\Common\User;
use App\Models\Common\Card;
use App\Models\Common\Reason;
use App\Models\Common\AdminService;
use App\Models\Common\UserRequest;
use App\Models\Common\PaymentLog;
use App\Services\PaymentGateway;
use App\Http\Controllers\V1\Service\Provider\ServeController;
use App\Http\Controllers\V1\Common\Provider\HomeController;
use App\Http\Controllers\V1\Common\CommonController;
use App\Models\Common\MenuCity;
use App\Models\Common\Menu;
use App\Models\Common\CompanyCity;
use Carbon\Carbon;
use Auth;
use Admin;
use DB;
use Razorpay\Api\Api;

class ServiceController extends Controller
{
	public function providerServiceList(Request $request)
	{
		$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('user')->user()->company_id)->first()->settings_data));

		$siteConfig = $settings->site;
		$serviceConfig = $settings->service;

		$distance = $serviceConfig->provider_search_radius ? $serviceConfig->provider_search_radius : 100;
	   
		$latitude = $request->lat;
		$longitude = $request->long;
		$service_id = $request->id;
		
		//$timezone =  (Auth::guard('user')->user()->state_id) ? State::find(Auth::guard('user')->user()->state_id)->timezone : '';
		// $currency =  Country::find(Auth::guard('user')->user()->country_id) ? Country::find(Auth::guard('user')->user()->country_id)->country_currency : '' ;

		$admin_service = AdminService::where('admin_service','SERVICE')->where('company_id', Auth::guard('user')->user()->company_id)->first();

		$currency = CompanyCountry::where('company_id',Auth::guard('user')->user()->company_id)->where('country_id',Auth::guard('user')->user()->country_id)->first();
		$service_cancel_provider = ServiceCancelProvider::select('id','provider_id')->where('company_id',Auth::guard('user')->user()->company_id)->where('user_id',Auth::guard('user')->user()->id)->pluck('provider_id','provider_id')->toArray();
	
		$admin_id=$admin_service->id;
		$callback = function ($q) use ($admin_id,$service_id) {
			$q->where('admin_service','SERVICE');
			$q->where('service_id',$service_id);
		};

  
		$provider_service_init = Provider::with(['service'=> $callback,'service_city'=>function($q) use ($service_id){
			return $q->where('service_id',$service_id);

		},'request_filter'])
		->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id','first_name','last_name','picture','rating','city_id','latitude','longitude')
		->where('status', 'approved')
		->where('is_online',1)->where('is_assigned',0)
		->where('company_id', Auth::guard('user')->user()->company_id)
		->where('city_id', Auth::guard('user')->user()->city_id)
		->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
		->whereDoesntHave('request_filter')
		->whereHas('service', function($q) use ($admin_id, $service_id){          
			$q->where('admin_service','SERVICE');
			$q->where('service_id',$service_id);
		})
		->where('wallet_balance' ,'>=',$siteConfig->provider_negative_balance);
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
				$serviceDetails=Service::with('serviceCategory')->where('id',$service_id)->where('company_id',Auth::guard('user')->user()->company_id)->first();
				foreach($provider_service as $key=> $service){ 
					unset($service->request_filter);
					$provider = new \stdClass();                   
					$provider->distance=$service->distance;
					$provider->id=$service->id;
					$provider->first_name=$service->first_name;
					$provider->last_name=$service->last_name;
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
							if($service->service_city->fare_type == "DISTANCETIME") {
									$provider->per_mins=Helper::decimalRoundOff($service->service_city->per_mins);
							} else {
									$provider->per_mins=Helper::decimalRoundOff($service->service_city->per_mins*60);
							}						   

					   }
					   else{
						   if(!empty($request->qty))
							   $provider->base_fare=Helper::decimalRoundOff($service->service->base_fare*$request->qty);
						   else
							   $provider->base_fare=Helper::decimalRoundOff($service->service->base_fare);

						   $provider->per_miles=Helper::decimalRoundOff($service->service->per_miles);
						   if($service->service_city->fare_type == "DISTANCETIME") {
								$provider->per_mins=Helper::decimalRoundOff($service->service->per_mins);
							} else {
								$provider->per_mins=Helper::decimalRoundOff($service->service->per_mins*60);
							}						   
					   }

		
						$provider->price_choose=$serviceDetails->serviceCategory->price_choose;

						$provider->commission=($provider->base_fare*$service->service_city->commission)/100;

						$provider->tax=(($provider->base_fare+$provider->commission)*$service->service_city->tax)/100;
						
						$provider->total_fare = Helper::decimalRoundOff($provider->base_fare + $provider->tax + $provider->commission);
					}    
											
					$providers[] = $provider;
				}                

			}       

			return Helper::getResponse(['data' =>['provider_service' => $providers,'currency' => ($currency != null) ? $currency->currency: '']]);

		}
	}

	public function review(Request $request,$id)
	{
		if($request->has('limit')) {
			$review = Rating::select('id','admin_service','user_id','provider_id','provider_rating','provider_comment','user_comment','user_rating','created_at')->where('provider_id',$id)->where(['company_id'=>Auth::guard('user')->user()->company_id])->where('admin_service','SERVICE')->whereNotNull('user_comment')->where('user_comment','!=',"")
			->with([
					'user' => function($query){  $query->select('id', 'first_name', 'last_name', 'picture' ); },
			])->take($request->limit)->offset($request->offset)->orderby('id','desc')->get();
		}else{
			$review = Rating::select('id','admin_service','user_id','provider_id','provider_rating','provider_comment','user_comment','user_rating','created_at')->where('provider_id',$id)->where(['company_id'=>Auth::guard('user')->user()->company_id])->where('admin_service','SERVICE')
			->with([
					'user' => function($query){  $query->select('id', 'first_name', 'last_name', 'picture' ); },
			])->orderby('id','desc')->get();
		}
		$jsonResponse['total_records'] = count($review);
		$jsonResponse['review'] = $review;
		if($jsonResponse){
			return Helper::getResponse(['data' =>$jsonResponse]);
		}
	}

	public function service(Request $request,$id)
	{
		$service = Service::where('id',$id)->where(['company_id'=>Auth::guard('user')->user()->company_id])->first();
        if($service){
            return Helper::getResponse(['data' =>$service]);
        }
	}

	/*public function cancel_request(Request $request,$id)
	{
		try{
			//add cancel_request
			$service_cancel_provider = new ServiceCancelProvider;
			$service_cancel_provider->company_id = Auth::guard('user')->user()->company_id;;
			$service_cancel_provider->user_id = Auth::guard('user')->user()->id;;
			$service_cancel_provider->provider_id = $id;
			$service_cancel_provider->service_id = 1;
			$service_cancel_provider->save();
			return Helper::getResponse(['message' => trans('Cancel the Provider request')]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' => $e->getMessage() ]);
		}
	}*/

	//For Promocode
	public function promocode(Request $request)
	{
		$promocodes = Promocode::where('company_id', Auth::guard('user')->user()->company_id)->where('service', 'SERVICE')
			->where('expiration','>=',date("Y-m-d H:i"))
			->whereDoesntHave('promousage', function($query) {
				$query->where('user_id',Auth::guard('user')->user()->id);
			})
			->get();

		return Helper::getResponse(['data' => $promocodes]);
	}

	//Create the ride
	public function create_service(Request $request)
	{
		$this->validate($request, [
			'service_id' => 'required|integer|exists:service.services,id',
			's_latitude' => 'required',
			's_longitude' => 'required',
			'payment_mode' => 'required',
			'id' => 'required',
		]);

		try {
			$service = (new Services())->create_service($request);
			return Helper::getResponse(['status' => isset($service['status']) ? $service['status'] : 200, 'message' => $service['message'] ? $service['message'] : '', 'data' => isset($service['data']) ? $service['data'] : [] ]);
		} catch (Exception $e) { 
			\Log::info('error1');
		\Log::info($e); 
			return Helper::getResponse(['status' => 500, 'message' => trans('api.service.request_not_completed'), 'error' => $e->getMessage() ]);
		}
	}

	public function update_service(Request $request,$id)
	{
		$update_service = Service::where('id',$id)->update(['allow_desc' =>'0']);
		return Helper::getResponse(['data' => $update_service]);
	}

	public function cancel_service(Request $request)
	{
		$this->validate($request, [
			'id' => 'required|numeric|exists:service.service_requests,id,user_id,'.Auth::guard('user')->user()->id,
		]);

		$request->request->add(['cancelled_by' => 'USER']);

		try {
			$service = (new Services())->cancelService($request);
			return Helper::getResponse(['status' => isset($service['status']) ? $service['status'] : 200, 'message' => $service['message'] ? $service['message'] : '', 'data' => isset($service['data']) ? $service['data'] : [] ]);
		} catch (Exception $e) {  
			return Helper::getResponse(['status' => 500, 'message' => trans('api.service.request_not_completed'), 'error' => $e->getMessage() ]);
		}
	}

	//status
	public function status(Request $request)
	{
		try{

			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('user')->user()->company_id)->first()->settings_data));

			$siteConfig = $settings->site;

			$serviceConfig = $settings->service;

			$check_status = ['CANCELLED', 'SCHEDULED'];
			$serviceRequest = ServiceRequest::ServiceRequestStatusCheck(Auth::guard('user')->user()->id, $check_status)
							  ->get();
									   
			$search_status = ['SEARCHING','SCHEDULED'];
			$serviceRequestFilter = ServiceRequest::ServiceRequestAssignProvider(Auth::guard('user')->user()->id,$search_status)->get(); 
			$Timeout = $serviceConfig->provider_select_timeout ? $serviceConfig->provider_select_timeout : 60 ;
			$response_time = $Timeout;
			if(!empty($serviceRequest)){
				// $serviceRequest[0]['ride_otp'] = (int) $serviceConfig->serve_otp ? $serviceConfig->serve_otp : 0 ;

				// $serviceRequest[0]['reasons']=Reason::where('type','USER')->get();
				// $categoryId = $serviceRequest[0]['service']['service_category_id'];
				foreach($serviceRequest as $key=>$requestlist){
					$categoryId = $requestlist->service->service_category_id;
					$subCategoryId = $requestlist->service->service_subcategory_id;
					$requestlist->category = ServiceCategory::where('id',$categoryId)->first();
					$requestlist->subcategory = ServiceSubCategory::where('id',$subCategoryId)->first();
					$requestlist->reasons =Reason::where('type','USER')->get();
					$response_time = $Timeout - (time() - strtotime($serviceRequest[$key]->assigned_at));
				}
				
			}

		   
			

			/*if(!empty($serviceRequestFilter)){
				for ($i=0; $i < sizeof($serviceRequestFilter); $i++) {
					$ExpiredTime = $Timeout - (time() - strtotime($serviceRequestFilter[$i]->assigned_at));
					if($serviceRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
						$Providertrip = new HomeController();
						$Providertrip->assign_next_provider($serviceRequestFilter[$i]->id);
						$response_time = $Timeout - (time() - strtotime($serviceRequestFilter[$i]->assigned_at));
					}else if($serviceRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
						break;
					}
				}

			}*/
			if(empty($serviceRequest)) {

				$cancelled_request = ServiceRequest::where('service_requests.user_id', Auth::guard('user')->user()->id)
					->where('service_requests.user_rated',0)
					->where('service_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
					->where('updated_at','>=',\Carbon\Carbon::now()->subSeconds(5))
					->first();
				
			}
			return Helper::getResponse(['data' => [
				'response_time' => $response_time, 
				'data' => $serviceRequest, 
				'sos' => isset($siteConfig->sos_number) ? $siteConfig->sos_number : '911' , 
				'emergency' => isset($siteConfig->contact_number) ? $siteConfig->contact_number : [['number' => '911']]  ]]);

		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
		}
	}

	public function checkService(Request $request, $id)
	{
		try{

			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('user')->user()->company_id)->first()->settings_data));

			$siteConfig = $settings->site;

			$serviceConfig = $settings->service;

			$check_status = ['CANCELLED', 'SCHEDULED'];
			$serviceRequest = ServiceRequest::ServiceRequestStatusCheck(Auth::guard('user')->user()->id, $check_status)
							  ->where('id', $id)
							  ->get();
									   
			$search_status = ['SEARCHING','SCHEDULED'];
			$serviceRequestFilter = ServiceRequest::ServiceRequestAssignProvider(Auth::guard('user')->user()->id,$search_status)->get(); 
			$Timeout = $serviceConfig->provider_select_timeout ? $serviceConfig->provider_select_timeout : 60 ;
			$response_time = $Timeout;
			if(!empty($serviceRequest)){
				// $serviceRequest[0]['ride_otp'] = (int) $serviceConfig->serve_otp ? $serviceConfig->serve_otp : 0 ;

				// $serviceRequest[0]['reasons']=Reason::where('type','USER')->get();
				// $categoryId = $serviceRequest[0]['service']['service_category_id'];
				foreach($serviceRequest as $key=>$requestlist){
					$categoryId = $requestlist->service->service_category_id;
					$subCategoryId = $requestlist->service->service_subcategory_id;
					$requestlist->category = ServiceCategory::where('id',$categoryId)->first();
					$requestlist->subcategory = ServiceSubCategory::where('id',$subCategoryId)->first();
					$requestlist->reasons =Reason::where('type','USER')->get();
					$response_time = $Timeout - (time() - strtotime($serviceRequest[$key]->assigned_at));
				}
				
			}

		   
			

			/*if(!empty($serviceRequestFilter)){
				for ($i=0; $i < sizeof($serviceRequestFilter); $i++) {
					$ExpiredTime = $Timeout - (time() - strtotime($serviceRequestFilter[$i]->assigned_at));
					if($serviceRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
						$Providertrip = new HomeController();
						$Providertrip->assign_next_provider($serviceRequestFilter[$i]->id);
						$response_time = $Timeout - (time() - strtotime($serviceRequestFilter[$i]->assigned_at));
					}else if($serviceRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
						break;
					}
				}

			}*/
			if(empty($serviceRequest)) {

				$cancelled_request = ServiceRequest::where('service_requests.user_id', Auth::guard('user')->user()->id)
					->where('service_requests.user_rated',0)
					->where('service_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
					->where('updated_at','>=',\Carbon\Carbon::now()->subSeconds(5))
					->first();
				
			}
			return Helper::getResponse(['data' => [
				'response_time' => $response_time, 
				'data' => $serviceRequest, 
				'sos' => isset($siteConfig->sos_number) ? $siteConfig->sos_number : '911' , 
				'emergency' => isset($siteConfig->contact_number) ? $siteConfig->contact_number : [['number' => '911']]  ]]);

		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
		}
	}


	public function update_payment_method(Request $request)
	{
		$this->validate($request, [
			'id' => 'required|exists:service.service_requests',
			'payment_mode' => 'required',
		]);

		try{

			if($request->has('card_id')){
				Card::where('user_id',Auth::guard('user')->user()->id)->update(['is_default' => 0]);
				Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
			}

			$serviceRequest = ServiceRequest::findOrFail($request->id);
			$serviceRequest->payment_mode = $request->payment_mode;

			if($request->payment_mode != "CASH") {
				$serviceRequest->status = 'DROPPED';
				$serviceRequest->save();
			}

			$serviceRequest->save();

			$payment = ServiceRequestPayment::where('service_request_id', $serviceRequest->id)->first();

			if($payment != null) {
				$payment->payment_mode = $request->payment_mode;
				$payment->save();
			}

			$admin_service = AdminService::where('admin_service', 'SERVICE')->where('company_id', Auth::guard('user')->user()->company_id)->first();

			$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'SERVICE' )->first();
			$user_request->request_data = json_encode($serviceRequest);
			$user_request->save();

			//Send message to socket
            $requestData = ['type' => $user_request->admin_service, 'id' => $request->id, 'room' => 'room_'.Auth::guard('user')->user()->company_id, 'payment_mode' => $request->payment_mode];
            app('redis')->publish('paymentUpdate', json_encode( $requestData ));

			(new SendPushNotification)->updateProviderStatus($user_request->provider_id, 'provider', trans('api.service.payment_updated'), 'Payment Mode Changed', '' ); 

			return Helper::getResponse(['message' => trans('api.service.payment_updated')]);
		}

		catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function rate(Request $request) 
	{

		$this->validate($request, [
				'rating' => 'required',
				'comment' => 'max:255',
			],['comment.max'=>'character limit should not exceed 255']);
	
		$serviceRequest = ServiceRequest::findOrFail($request->id);
		if ($serviceRequest->paid == 0) {

		  return Helper::getResponse(['status' => 422, 'message' => trans('api.user.not_paid'), 'error' => trans('api.user.not_paid')  ]);
		}
		try{
			$admin_service = AdminService::where('admin_service', 'SERVICE')->where('company_id', Auth::guard('user')->user()->company_id)->first();

			$serviceRequest = ServiceRequest::findOrFail($request->id);

			$ratingRequest = Rating::where('request_id', $serviceRequest->id)
					 ->where('admin_service', 'SERVICE' )->first();
			
			if($ratingRequest == null) {
				$request->request->add(['company_id' => $serviceRequest->company_id ]);
				$request->request->add(['provider_id' => $serviceRequest->provider_id ]);
				$request->request->add(['user_id' => $serviceRequest->user_id ]);
				$request->request->add(['request_id' => $serviceRequest->id ]);
				(new \App\Http\Controllers\V1\Common\CommonController)->rating($request);
			} else {
				$ratingRequest->update([
					  'provider_rating' => $request->rating,
					  'provider_comment' => $request->comment,
					]);
			}
			$serviceRequest->user_rated = 1;            
			$serviceRequest->save();
  
			$average = Rating::where('provider_id', $serviceRequest->provider_id)->avg('user_rating');
			
			$User = User::find($serviceRequest->user_id);
			$User->rating=$average;
			$User->save();

			// Send Push Notification to Provider
			return Helper::getResponse(['message' => trans('api.service.service_rated') ]);
  
		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_completed'), 'error' => $e->getMessage() ]);
		}
	  }

	 public function payment(Request $request) {
		 
		 try {
  
			  $tip_amount = 0;
  
			  $serviceRequest = \App\Models\Service\ServiceRequest::find($request->id);
			  $payment = \App\Models\Service\ServiceRequestPayment::where('service_request_id', $request->id)->first();
  
			  $user = User::find($serviceRequest->user_id);
			  $setting = Setting::where('company_id', $user->company_id)->first();
			  $settings = json_decode(json_encode($setting->settings_data));
			  $siteConfig = $settings->site;
			  $serviceConfig = $settings->service;
			  $paymentConfig = json_decode( json_encode( $settings->payment ) , true);

			  $cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'RAZORPAY'; }));
			  $card = 0;

				$stripe_secret_key = "";
				$stripe_publishable_key = "";
				$stripe_currency = "";

				if(count($cardObject) > 0) { 
					$card = $cardObject[0]['status'];

					$stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_secret_key'; }));
					$stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_api_key'; }));
					$stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_currency'; }));

					if(count($stripeSecretObject) > 0) {
						$stripe_secret_key = $stripeSecretObject[0]['value'];
					}

					if(count($stripePublishableObject) > 0) {
						$stripe_publishable_key = $stripePublishableObject[0]['value'];
					}

					if(count($stripeCurrencyObject) > 0) {
						$stripe_currency = $stripeCurrencyObject[0]['value'];
					}
				}
  
			  $random = $serviceConfig->booking_prefix.mt_rand(100000, 999999);
  
			  if (isset($request->tips) && !empty($request->tips)) {
				  $tip_amount = round($request->tips, 2);
			  }
  
			  $totalAmount = $payment->payable + $tip_amount;
  
			  $paymentMode = $request->has('payment_mode') ? strtoupper($request->payment_mode) : $serviceRequest->payment_mode;
			  if($request->payment_mode != "CASH") {
  
				  if ($totalAmount == 0) {
  
					  $serviceRequest->payment_mode = $paymentMode;
					  $payment->card = $payment->payable;
					  $payment->payable = 0;
					  $payment->tips = $tip_amount;
					  $payment->provider_pay = $payment->provider_pay + $tip_amount;
					  $payment->save();
  
					  $serviceRequest->paid = 1;
					  $serviceRequest->status = 'COMPLETED';
					  $serviceRequest->save();

					   $requestData = ['type' => 'SERVICE', 'room' => 'room_'.$serviceRequest->company_id, 'id' => $serviceRequest->id, 'city' => ($setting->demo_mode == 0) ? $serviceRequest->city_id : 0, 'user' => $serviceRequest->user_id ];
						app('redis')->publish('checkServiceRequest', json_encode( $requestData ));
  
					  return Helper::getResponse(['message' => trans('api.paid')]);
  
				  } else {
  
					  $log = new PaymentLog();
					  $log->admin_service = 'SERVICE';
					  $log->company_id = $user->company_id;
					  $log->user_type = 'user';
					  $log->transaction_code = $random;
					  $log->amount = $totalAmount;
					  $log->transaction_id = $serviceRequest->id;
					  $log->payment_mode = $paymentMode;
					  $log->user_id = $serviceRequest->user_id;
					  $log->save();
					  switch ($paymentMode) {
						  case 'RAZORPAY':
						      
						     $api = new Api('rzp_test_AbxUJ2sx4IaEwO', '81SQWWfRy2gAJ2MsWb8zJ9wS');
				            //Fetch payment information by razorpay_payment_id
				             $response = $api->payment->fetch($request->payment_id);
				           
                           
				             if( !empty($request->payment_id)) {
			            		 try {
										  if($response->status == "authorized") {

											$payment->payment_id = $request->payment_id;
											$payment->payment_mode = $paymentMode;
											$payment->card = $payment->payable;
											//$payment->payable = 0;
											$payment->tips = $tip_amount;
											$payment->total = $totalAmount;
											$payment->provider_pay = $payment->provider_pay + $tip_amount;
											$payment->save();

											 $serviceRequest->paid = 1;
											 $serviceRequest->status = 'COMPLETED';
											 $serviceRequest->save();
											//for create the transaction
											  (new ServeController)->callTransaction($serviceRequest->id);
											  $requestData = ['type' => 'SERVICE', 'room' => 'room_'.$serviceRequest->company_id, 'id' => $serviceRequest->id, 'city' => ($setting->demo_mode == 0) ? $serviceRequest->city_id : 0, 'user' => $serviceRequest->user_id ];
												app('redis')->publish('checkServiceRequest', json_encode( $requestData ));

											  return Helper::getResponse(['message' => trans('api.paid')]); 

										} else {
											return trans('Transaction Failed');
										}

								 } catch (\Exception $e) {
						               return Helper::getResponse(['message' => trans('Transaction Failed')]);
						         }

		            // Do something here for store payment details in database...
		        				}
  
  
						  break;
					  }
					  
				  }
  
			  } else {
				  $serviceRequest->paid = 1;
				  $serviceRequest->save();
				  $requestData = ['type' => 'SERVICE', 'room' => 'room_'.$serviceRequest->company_id, 'id' => $serviceRequest->id, 'city' => ($setting->demo_mode == 0) ? $serviceRequest->city_id : 0, 'user' => $serviceRequest->user_id ];
				app('redis')->publish('checkServiceRequest', json_encode( $requestData ));
			  }  
		  } catch (\Throwable $e) {
			   return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' => $e->getMessage() ]);
		  }
	  }

	public function searchServiceDispute(Request $request)
	{
		$results=array();
		$term =  $request->input('stext');
		if($request->input('sflag')==1){			
			$queries = ServiceRequest::where('provider_id', $request->id)->with('service')->orderby('id', 'desc')->take(10)->get();
		}else{
			$queries = ServiceRequest::where('user_id', $request->id)->with('service')->orderby('id', 'desc')->take(10)->get();
		}
		foreach ($queries as $query)
		{
			$RequestDispute = ServiceRequestDispute::where('service_request_id',$query->id)->first();
			if(!$RequestDispute){
				$results[]=$query;
			}
		}
		return response()->json(array('success' => true, 'data'=>$results));
	}

	public function requestHistory(Request $request)
	{
		try {
			$history_status = array('CANCELLED','COMPLETED');
			$datum = ServiceRequest::where('company_id', Auth::user()->company_id)
					 ->whereIn('status',$history_status)
					 ->with('payment','user', 'provider','rating');
			if(Auth::user()->hasRole('FLEET')) {
				$datum->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$datum->ServiceSearch($request->search_text);
			}    
			if($request->has('order_by')) {
				$datum->orderby($request->order_by, $request->order_direction);
			}
			$data = $datum->paginate(10);    
			return Helper::getResponse(['data' => $data]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}
	public function requestScheduleHistory(Request $request)
	{
		try {
			$scheduled_status = array('SCHEDULED');
			$datum = ServiceRequest::where('company_id', Auth::user()->company_id)
					->whereIn('status',$scheduled_status)
					 ->with('user', 'provider');
			if(Auth::user()->hasRole('FLEET')) {
				$datum->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$datum->Search($request->search_text);
			}    
			if($request->has('order_by')) {
				$datum->orderby($request->order_by, $request->order_direction);
			}
			$data = $datum->paginate(10);    
			return Helper::getResponse(['data' => $data]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}
	
	public function requestStatementHistory(Request $request)
	{
		try {
			$history_status = array('CANCELLED','COMPLETED');
			$serviceRequests = ServiceRequest::where('company_id',  Auth::user()->company_id)
					 ->with('user', 'provider','service.serviceCategory');

			if(Auth::user()->hasRole('FLEET')) {
				$serviceRequests->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$serviceRequests->ServiceSearch($request->search_text);
			}
			if($request->has('status') && $request->status != null) {
				$history_status = array($request->status);
			}

			if($request->has('country_id') && $request->country_id != null) {
				$serviceRequests->where('country_id',$request->country_id);
			}

			if($request->has('user_id') && $request->user_id != null) {
				$serviceRequests->where('user_id',$request->user_id);
			}

			if($request->has('provider_id') && $request->provider_id != null) {
				$serviceRequests->where('provider_id',$request->provider_id);
			}

			if($request->has('ride_type') && $request->ride_type != null) {
				$serviceRequests->whereHas('service.serviceCategory',function($q) use ($request){

					return $q->where('id',$request->ride_type);

				});
			}
	
			if($request->has('order_by')) {
				$serviceRequests->orderby($request->order_by, $request->order_direction);
			}
			$type = isset($_GET['type'])?$_GET['type']:'';
			if($type == 'today'){
				$serviceRequests->where('created_at', '>=', Carbon::today());
			}elseif($type == 'monthly'){
				$serviceRequests->where('created_at', '>=', Carbon::now()->month);
			}elseif($type == 'yearly'){
				$serviceRequests->where('created_at', '>=', Carbon::now()->year);
			}elseif ($type == 'range') {   
				if($request->has('from') &&$request->has('to')) {             
					if($request->from == $request->to) {
						$serviceRequests->whereDate('created_at', date('Y-m-d', strtotime($request->from)));
					} else {
						$serviceRequests->whereBetween('created_at',[Carbon::createFromFormat('Y-m-d', $request->from),Carbon::createFromFormat('Y-m-d', $request->to)]);
					}
				}
			}else{
				// dd(5);
			}
			$cancelservices = $serviceRequests;
			$orderCounts = $serviceRequests->count();
			$dataval = $serviceRequests->whereIn('status',$history_status)->paginate(10);
			$cancelledQuery = $cancelservices->where('status','CANCELLED')->count();
			$total_earnings = 0;

			foreach($dataval as $key=>$service){
				//$service->status = $service->status == 1?'Enabled' : 'Disable';
				$serviceid  = $service->id;
				$earnings = ServiceRequestPayment::select('total')->where('service_request_id',$serviceid)->where('company_id',  Auth::user()->company_id)->first();
				if($earnings != null){
					$dataval[$key]->revenue_value = $earnings->total;
					$service->earnings = $earnings->total;
					$total_earnings = $total_earnings + $earnings->total;
				}else{
					$dataval[$key]->revenue_value = 0;
					$service->earnings = 0;
				}
				// if(isset($service['service'])){
				// 	$service->forget('service');
				// }
			}
			$data['services'] = $dataval;
			$data['total_services'] = $orderCounts;
			$data['revenue_value'] = $total_earnings;
			$data['cancelled_services'] = $cancelledQuery;
			\Log::info($data);
			return Helper::getResponse(['data' => $data]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}
	public function requestHistoryDetails($id)
	{
		try {
			$data = ServiceRequest::with('user', 'provider','rating','service','serviceCategory')->findOrFail($id);
			return Helper::getResponse(['data' => $data]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function webproviderservice(Request $request,$id)
	{
	 
	 try{
		$storetype=Service::with(array('provideradminservice'=>function($query) use ($id){
			$query->where('provider_id',$id);
		}))->with('serviceCategory','servicesubCategory')->where('company_id',Auth::user()->company_id)->get();

		return Helper::getResponse(['data' => $storetype ]);
	} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
	}

	}

	public function getcity(Request $request)
	{
		 //dd($request->city_id);
		$menudetails=Menu::select('menu_type_id')->where('id',$request->menu_id)->first();
		// $service_data=Service::findorfail($menudetails->menu_type_id);
	   
		$serviceprice=ServiceCityPrice::select('city_id')->whereHas('service', function($query) use($menudetails){
                   $query->where('service_category_id',$menudetails->menu_type_id);
             })->get()->toArray();
		$company_cities = CompanyCity::with(['country','city','menu_city' => function($query) use($request) {
			$query->where('menu_id','=',$request->menu_id);
		}])->where('company_id', Auth::user()->company_id);

		if($request->has('search_text') && $request->search_text != null) {
			$company_cities = $company_cities->Search($request->search_text);
		}
		$cities = $company_cities->paginate(500);

		foreach($cities as $key=>$value){

		   $cities[$key]['city_price']=0;
		   
		   if(in_array($value->city_id,array_column($serviceprice,'city_id'))){
			
			 $cities[$key]['city_price']=1;
		   } 
		}


		return Helper::getResponse(['data' => $cities]);
	}


}