<?php

namespace App\Http\Controllers\V1\Common\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Common\Setting;
use App\Models\Common\Provider;
use App\Models\Common\CountryBankForm;
use App\Models\Common\ProviderCard;
use App\Models\Common\ProviderVehicle;
use App\Models\Common\ProviderService;
use App\Models\Common\ProviderWallet;
use App\Models\Common\RequestFilter;
use App\Models\Common\AdminService;
use App\Models\Common\ProviderBankdetail;
use App\Models\Common\ProviderDocument;
use App\Models\Common\Document;
use App\Models\Service\ServiceRequest;
use App\Services\SendPushNotification; 
use App\Models\Common\CompanyCountry;
use App\Models\Common\Notifications;
use App\Models\Common\State;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\CompanyCity;
use App\Models\Common\UserRequest;
use App\Models\Service\Service;
use App\Models\Common\Reason;
use App\Models\Common\Admin;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreOrderDispute;
use App\Services\ReferralResource;
use App\Services\V1\Common\ProviderServices;
use App\Models\Common\Chat;
use App\Helpers\Helper; 
use Carbon\Carbon;
use Auth;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use DB;

class HomeController extends Controller
{
	use Encryptable;

	public function index(Request $request)
	{
		try{

			$Response = (new ProviderServices())->checkRequest($request);

			return Helper::getResponse(['data' => $Response ]);
		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function accept_request(Request $request) 
	{
		$this->validate($request, [
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
        ]);

		try {

			$Response = (new ProviderServices())->acceptRequest($request);
			
			return Helper::getResponse(['data' => $Response  ]);    
		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'message' => $e->getMessage() ]);
		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => $e->getMessage() ]);
		}
	}

	public function cancel_request(Request $request) {

		$this->validate($request, [
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
        ]);

		try {
			$Response = (new ProviderServices())->cancelRequest($request);
			return Helper::getResponse(['message' => $Response  ]); 
		} catch(\Throwable $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage() ]);
		}  
	}
	
	public function show_profile()
	{
		$provider_details = Provider::with('service','country','state','city')->where('id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->first();
		// dd($provider_details);
		 $provider_details['referral']=(object)array();
   
		$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));
		$siteConfig = $settings->site;
		if($provider_details->wallet_balance >= $siteConfig->provider_negative_balance){
			$provider_details['is_walletbalance_min'] = 1;
		}else{
			$provider_details['is_walletbalance_min'] = 0;
		}
		if($settings->site->referral==1){
			 $provider_details['referral']->referral_code=$provider_details['referral_unique_id'];        
			   $provider_details['referral']->referral_amount= Helper::decimalRoundOff($settings->site->referral_amount);
			$provider_details['referral']->referral_count=(int)$settings->site->referral_count;
			$provider_details['referral']->user_referral_count = (int)(new ReferralResource)->get_referral(2, Auth::guard('provider')->user()->id)[0]->total_count;
			$provider_details['referral']->user_referral_amount =(new ReferralResource)->get_referral(2, Auth::guard('provider')->user()->id)[0]->total_amount;
		}


		return Helper::getResponse(['data' => $provider_details]);
	}

	public function update_location(Request $request)
	{
		$this->validate($request, [
			'provider_id' => 'required|numeric',
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
	    ]);

		try {
			Provider::where('id', $request->provider_id)->update([
				'latitude' => $request->latitude,
				'longitude' => $request->longitude,
			]);

			return Helper::getResponse(['message' => 'Successfully updated'  ]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 500, 'message' => $e->getMessage() ]);
		}
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Provider  $provider
	 * @return \Illuminate\Http\Response
	 */
	public function update_profile(Request $request)
	{
		if($request->has('mobile')) {
			$request->merge([
				'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
			]);
			$mobile=$request->mobile;
			$company_id=Auth::guard('provider')->user()->company_id;
			$id=Auth::guard('provider')->user()->id;

			$this->validate($request, [          
				'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id,$id) {
					return $query->where('mobile', $mobile)->where('company_id', $company_id)->whereNotIn('id', [$id]);
				})],
			]);

			$request->merge([
				'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
			]); 

		}

		
		try{
			$provider = Provider::where('id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->first();

			if($request->has('first_name')) $provider->first_name = $request->first_name;

			if($request->has('last_name')) $provider->last_name = $request->last_name;
				
			if($request->has('email')) $provider->email = $request->email;

			if($request->has('language')) $provider->language = $request->language;

			if($request->has('gender')) $provider->gender = $request->gender;

			if($request->has('mobile')) $provider->mobile = $request->mobile;

			
			if($request->has('city_id')) $provider->city_id = $request->city_id;

			if($request->has('city_id')) $provider->city_id = $request->city_id;
			
			if($request->has('country_code')) $provider->country_code = $request->country_code;

			if($request->has('latitude')) $provider->latitude = $request->latitude;

			if($request->has('longitude')) $provider->longitude = $request->longitude;

			if($request->has('current_location')) $provider->current_location = $request->current_location;
			

			if($request->hasFile('picture')) $provider->picture = Helper::upload_file($request->file('picture'), 'provider', null, Auth::guard('provider')->user()->company_id);

			$provider->save();

			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update'), 'data' => $provider]);
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
	public function password_update(Request $request)
	{
		$this->validate($request,[
			'old_password' => 'required',
			'password' => 'required|min:6|different:old_password',
			'password_confirmation' => 'required'
		],['password.different'=>'The new password and old password should not be same']);
		 
		try {

			$provider =Provider::where('id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->first();
			if(password_verify($request->old_password, $provider->password))
			{   
				$provider->password = Hash::make($request->password);
				$provider->save();
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
			} else {

				return Helper::getResponse(['status' => 422, 'message' => trans('admin.old_password_incorrect')]);
			}
			
		}  catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}



	public function addcard(Request $request)
	{
		$this->validate($request,[
			'stripe_token' => 'required', 
		]);

		try{

			$customer_id = $this->customer_id();
			$this->set_stripe();
			$customer = \Stripe\Customer::retrieve($customer_id);
			$card = $customer->sources->create(["source" => $request->stripe_token]);
			

			$user = Auth::guard('provider')->user();

			 $exist = ProviderCard::where('provider_id',$user->id)
						 ->where('last_four',$card['last4'])
						 ->where('brand',$card['brand'])
						 ->count();

			if($exist == 0){

				   $create_card = new ProviderCard;
					$create_card->provider_id = $user->id;
					$create_card->card_id = $card->id;
					$create_card->last_four = $card->last4;
					$create_card->brand = $card->brand;
					$create_card->company_id = $user->company_id;
					$create_card->month = $card->exp_month;
					$create_card->year = $card->exp_year;
					$create_card->holder_name = $card->name;
					$create_card->funding = $card->funding;               
					$create_card->save();
			 }else{
				return Helper::getResponse(['status' => 403, 'message' => trans('api.card_already')]);     
			}

			return Helper::getResponse(['message' => trans('api.card_added')]); 

			} catch(Exception $e){
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
			}
	}

	public function provider_services() {

		$services = [];

		try {
			$services["transport"] = \App\Models\Transport\RideDeliveryVehicle::select('id', 'vehicle_name', \DB::raw("'2 mins' AS estimated_time"))->where('company_id', Auth::guard('provider')->user()->company_id)->where('status', 1)->get();
		} catch(\Throwable $e) { }
		
		return Helper::getResponse(['data' => $services ]);
	}


	public function vehicle_list(Request $request) {
		$provider_vehicle = ProviderVehicle::with('vehicle_type','provider_service')->where('provider_id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->paginate('10');
		return Helper::getResponse(['data' => $provider_vehicle]);
	}

	public function deleteproviderdocument(Request $request,$id) {   
		Provider::where('id',Auth::guard('provider')->user()->id)->update(['is_document' => 0,'status'=> 'DOCUMENT','is_online' =>'0']); 
		ProviderDocument::where('id',$id)->delete();
		return Helper::getResponse(['message' => trans('Provider Document Deleted Successfully')]);
	}

	public function available(Request $request) {

		$this->validate($request,[
			'status' => 'in:ACTIVE,OFFLINE'
		]);

		$provider = Auth::guard('provider')->user();

		$service = ProviderService::where('provider_id', $provider->id )->first();

		if($provider->status == 'APPROVED') {
			$service->status = $request->status;
			$service->save();
		} else {
			return Helper::getResponse(['status' => 403, 'message' => trans('api.provider.not_approved') ]);
		}
		
		return Helper::getResponse(['data' => $service ]);
	}

	public function set_stripe(){

		$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));

		$paymentConfig = json_decode( json_encode( $settings->payment ) , true);;

		$cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
		$card = 0;

		$stripe_secret_key = "";
		$stripe_publishable_key = "";
		$stripe_currency = "";

		if(count($cardObject) > 0) { 
			$card = $cardObject[0]['status'];

			$stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
			$stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
			$stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

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


		return \Stripe\Stripe::setApiKey( $stripe_secret_key );
	}

	public function customer_id()
	{
		if(Auth::guard('provider')->user()->stripe_cust_id != null){
			return Auth::guard('provider')->user()->stripe_cust_id;
		}else{

			try{ 
				$stripe = $this->set_stripe();
				$customer = \Stripe\Customer::create([
					'email' => Auth::guard('provider')->user()->email,
				]);

		   Provider::where('id',Auth::guard('provider')->user()->id)->update(['stripe_cust_id' => $customer['id']]);
				return $customer['id'];

			} catch(Exception $e){
				return $e;
			}
		}
	}

	public function order_status(Request $request){

		$order_status = UserRequest::where('user_id',Auth::guard('user')->user()->id)
						->whereNotIn('status',['CANCELLED','SCHEDULED'])->get();
		return Helper::getResponse(['data' => $order_status]);
	}

	public function carddetail(Request $request)
	{
		$provider_cards = ProviderCard::where('provider_id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->get();
		return Helper::getResponse(['data' => $provider_cards]);    
	}

	public function deleteCard(Request $request,$id)
	{
		$provider_card = ProviderCard::where('id', $id)->first();
		if($provider_card){
			try {
				ProviderCard::where('id',$id)->delete();
				return Helper::getResponse(['message' => trans('api.card_deleted')]);
			} catch (Exception $e) {
				return Helper::getResponse(['status' => 422, 'message' => 'Card Not Found', 'error' => $e->getMessage()]);
			}
		}else{
			return Helper::getResponse(['status' => 422, 'message' => 'Card Not Found']);
		}
	}

	public function providerlist(){
		$provider_list = Provider::where('id',Auth::guard('provider')->user()->id)->where('company_id',Auth::guard('provider')->user()->company_id)->with('country')->first();
		return Helper::getResponse(['data' => $provider_list]);
	}

	public function walletlist(Request $request)
	{
		if($request->has('limit')) {
			$provider_wallet = ProviderWallet::select('id', 'transaction_id','transaction_alias',DB::raw('SUM(amount) as amount'), 'transaction_desc', 'type','created_at')->with(['transactions','payment_log' => function($query){  $query->select('id','company_id','is_wallet','user_type','payment_mode','user_id', 'transaction_code'); }])
			->where('company_id',Auth::guard('provider')->user()->company_id)
			->where('provider_id',Auth::guard('provider')->user()->id)->groupBy('transaction_alias')->orderBy('id', 'desc');
			$totalRecords = $provider_wallet->count();
			$provider_wallet = $provider_wallet->take($request->limit)->offset($request->offset)->get();
			$response['total_records'] = $totalRecords;
			$response['data'] = $provider_wallet;
			return Helper::getResponse(['data' => $response]);
		} else {
			$provider_wallet = ProviderWallet::select('id','provider_id', 'transaction_id','transaction_alias',DB::raw('SUM(amount) as amount'), 'transaction_desc', 'type', 'created_at')
			->with(['transactions','payment_log' => function($query){  $query->select('id','company_id','is_wallet','user_type','payment_mode','user_id','amount','transaction_code'); },'provider' => function($query){  $query->select('id','currency_symbol'); }])
			->where('company_id',Auth::guard('provider')->user()->company_id)
			->where('provider_id',Auth::guard('provider')->user()->id)->groupBy('transaction_alias');

			if($request->has('search_text') && $request->search_text != null) {
						$provider_wallet->Search($request->search_text);
			}
			$provider_wallet->orderby('id', 'desc');
			/*if($request->has('order_by')) {
				$provider_wallet->orderby($request->order_by, $request->order_direction);
			} */                   
			$provider_wallet=$provider_wallet->paginate(10);
			return Helper::getResponse(['data' => $provider_wallet]);
		}
	}


	public function countries(Request $request) {
		$company_id = base64_decode($request->salt_key);
		$country_list = CompanyCountry::with(['companyCountryCities' => function($q) use($company_id) {  $q->where('company_id', $company_id); }])->has('companyCountryCities')->where('company_id', $company_id )->where('status', 1)->get();
		$countries = [];
		foreach ($country_list as $country) {
			$object = new \stdClass();
			$object->id = $country->country->id;
			$object->country_name = $country->country->country_name;
			$object->country_code = $country->country->country_code;
			$object->country_phonecode = $country->country->country_phonecode;
			$object->country_currency = $country->country->country_currency;
			$object->country_symbol = $country->country->country_symbol;
			$object->status = $country->country->status;
			$object->timezone = $country->country->timezone;
			foreach ($country->companyCountryCities as $value) {
				$object->city[] = $value->city;
			}
			$countries[] = $object;
		}

		return Helper::getResponse(['data' => $countries]);
	}



	public function reasons(Request $request)
	{
		$reason = Reason::where('company_id', Auth::guard('provider')->user()->company_id)
					->where('service', $request->type)
					->where('type', 'PROVIDER')
					->where('status','active')
					->where('lang', $request->lang?:'en')
					->get();

		return Helper::getResponse(['data' => $reason]);

	}   

	public function adminservices(Request $request)
	{

		$adminservices_list = AdminService::with('providerservices')
		->where('company_id', Auth::guard('provider')->user()->company_id)
		->where('status', 1)
		->get();
		$adminservices = [];
		foreach ($adminservices_list as $key => $adminservice) {
			$data = new \stdClass;
			$documents = Document::with('provider_document')->where('company_id', Auth::guard('provider')->user()->company_id)->where('type',"SERVICE")->where('status',1)->get();
			$data->data = $adminservice;
			$data->data->documents = $documents;
			$adminservices[] = $data->data;
		}


		$data = new \stdClass;
		$documents = Document::with('provider_document')->where('company_id', Auth::guard('provider')->user()->company_id)->where('type','ALL')->where('status',1)->get();

		$adminservice = new \stdClass;
		$adminservice->admin_service = "COMMON";

		$data->data = $adminservice;
		$data->data->documents = $documents;
		$adminservices[] = $data->data;
		
		return Helper::getResponse(['data' => $adminservices]);

	}  

	public function addvechile(Request $request)
	{

		$this->validate($request, [
			 'vehicle_id' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_year' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_make' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'vehicle_model' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_no' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'admin_service' =>'required',
			'category_id' => 'required',
			'picture' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'picture1' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			
			],["picture.required_if"=>'Please Upload RC ',"picture1.required_if"=>'Please Upload Insurance ']);

		try{
			if($request->admin_service != 'SERVICE'){
				$providervehicle = new ProviderVehicle;
				$providervehicle->provider_id=Auth::guard('provider')->user()->id;
				if($request->has('vehicle_id')) $providervehicle->vehicle_service_id=$request->vehicle_id;

				$providervehicle->company_id=Auth::guard('provider')->user()->company_id;
				if($request->has('vehicle_year')) $providervehicle->vehicle_year=$request->vehicle_year;

				if($request->has('vehicle_make')) $providervehicle->vehicle_make=$request->vehicle_make;

				if($request->has('vehicle_model')) $providervehicle->vehicle_model=$request->vehicle_model;

				if($request->has('vehicle_color')) $providervehicle->vehicle_color=$request->vehicle_color;

				$providervehicle->vehicle_no=$request->vehicle_no;

				if($request->has('wheel_chair')) {
					$providervehicle->wheel_chair=$request->wheel_chair;
				}else{
					$providervehicle->wheel_chair=0;    
				}

				if($request->has('child_seat')) {
					$providervehicle->child_seat=$request->child_seat;
				}else{
					$providervehicle->child_seat=0;    
				}

				if($request->hasFile('vechile_image')) $providervehicle->vechile_image = Helper::upload_file($request->file('vechile_image'), 'provider', null, Auth::guard('provider')->user()->company_id);

				if($request->hasFile('picture')) $providervehicle->picture = Helper::upload_file($request->file('picture'), 'provider', null, Auth::guard('provider')->user()->company_id);

				if($request->hasFile('picture1')) $providervehicle->picture1 = Helper::upload_file($request->file('picture1'), 'provider', null, Auth::guard('provider')->user()->company_id);

				$providervehicle->save();

			}

			$providerservice=new  ProviderService;
			$providerservice->provider_id=Auth::guard('provider')->user()->id;
			$providerservice->company_id=Auth::guard('provider')->user()->company_id;
			if($request->admin_service != 'SERVICE') $providerservice->provider_vehicle_id=$providervehicle->id;

			$providerservice->admin_service=$request->admin_service;
			$providerservice->category_id=$request->category_id;
			if($request->admin_service=='TRANSPORT'){
				$providerservice->ride_delivery_id=$request->vehicle_id;
			} 
			else if($request->admin_service=='SERVICE'){
				foreach($request->service  as $key =>$val){
					$providerservice=new  ProviderService;    
					$providerservice->provider_id=Auth::guard('provider')->user()->id;
					$providerservice->company_id=Auth::guard('provider')->user()->company_id;
					$providerservice->admin_service=$request->admin_service;
					$providerservice->category_id=$request->category_id;
					$providerservice->sub_category_id=$request->sub_category_id;
					$providerservice->service_id=$val['service_id'];
					if(isset($val['base_fare'])) $providerservice->base_fare=$val['base_fare'];

					if(isset($val['per_miles'])) $providerservice->per_miles=$val['per_miles'];

					if(isset($val['per_mins'])) $providerservice->per_mins=$val['per_mins'];

					$providerservice->save();
				}
			}
			if($request->admin_service=='TRANSPORT' || $request->admin_service=='ORDER') {
			   $providerservice->save();
			}
			$documents=Document::where('type',$request->admin_service)->where('status',1)->count();
			if($documents>0){
	            $document=Document::whereHas('provider_document', function($query){

	            $query->where('provider_id',Auth::guard('provider')->user()->id);

	                })->where('type',$request->admin_service)->where('status',1)->count();
				$provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
				$provider->is_service=1;
				if($document==0){
					$provider->is_document=0;
					$provider->status='DOCUMENT';

					(new SendPushNotification)->updateProviderStatus($provider->id, 'provider', trans('admin.providers.status_changed'), 'Account Info', ['service' => $provider->is_service, 'document' => $provider->is_document, 'bank' => $provider->is_bankdetail] );
				}
				$provider->save();
		   }

			return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
		}
		catch (\Throwable $e) {

			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
		 
		
	  

	}

  


	public function editvechile(Request $request)
	{

		$this->validate($request, [
			'vehicle_id' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_year' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_make' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'id'            => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'vehicle_model' => ['required_if:admin_service,==,TRANSPORT'],
			'vehicle_no' => ['required_if:admin_service,==,TRANSPORT,ORDER'],
			'admin_service' =>'required',
			'category_id'   => 'required',
		]);


		try{
			if($request->admin_service !='SERVICE'){
			    $providervehicle = ProviderVehicle::findOrFail($request->id);
			    $providervehicle->provider_id=Auth::guard('provider')->user()->id;
			    $providervehicle->vehicle_service_id=$request->vehicle_id;
			    $providervehicle->company_id=Auth::guard('provider')->user()->company_id;
			    $providervehicle->vehicle_year=$request->vehicle_year;
			    $providervehicle->vehicle_make=$request->vehicle_make;
			    $providervehicle->vehicle_model=$request->vehicle_model;
			    $providervehicle->vehicle_no=$request->vehicle_no;
			    $providervehicle->vehicle_color=$request->vehicle_color;
			    if($request->has('wheel_chair')) {
			    	$providervehicle->wheel_chair=1;
			    }else{
			    	$providervehicle->wheel_chair=0;    
			    }
			    if($request->has('child_seat')) {
			    	$providervehicle->child_seat=1;
			    }else{
			    	$providervehicle->child_seat=0;    
			    }
			    if($request->hasFile('vechile_image')) $providervehicle->vechile_image = Helper::upload_file($request->file('vechile_image'), 'provider', null, Auth::guard('provider')->user()->company_id);

			    if($request->hasFile('picture')) $providervehicle->picture = Helper::upload_file($request->file('picture'), 'provider', null, Auth::guard('provider')->user()->company_id);

			    if($request->hasFile('picture1')) $providervehicle->picture1 = Helper::upload_file($request->file('picture1'), 'provider', null, Auth::guard('provider')->user()->company_id);

			    $providervehicle->save();

			}

			if($request->admin_service=='SERVICE'){
				ProviderService::where('admin_service','SERVICE')->where('sub_category_id',$request->sub_category_id)->where('category_id',$request->category_id)->where('provider_id',Auth::guard('provider')->user()->id)->delete();
				if($request->has("service")){
					foreach($request->service  as $key =>$val){
						$providerservice=new  ProviderService;    
						$providerservice->provider_id=Auth::guard('provider')->user()->id;
						$providerservice->company_id=Auth::guard('provider')->user()->company_id;
						$providerservice->admin_service=$request->admin_service;
						$providerservice->category_id=$request->category_id;
						$providerservice->sub_category_id=$request->sub_category_id;
						$providerservice->service_id=$val['service_id'];
						if(isset($val['base_fare'])) $providerservice->base_fare=$val['base_fare'];

						if(isset($val['per_miles'])) $providerservice->per_miles=$val['per_miles'];

						if(isset($val['per_mins'])) $providerservice->per_mins=$val['per_mins'];

						$providerservice->save();

				     $document = Document::where('company_id', Auth::guard('provider')->user()->company_id)->where('service_category_id',$request->category_id)->get();
                    // dd($document);
                   if(count($document)>0){
	                   	 foreach ($document as $key => $value) {
	                    	$provider_document=ProviderDocument::where('provider_id',Auth::guard('provider')->user()->id)->where('document_id',$value->id)->get();
	                    	if(count($provider_document)==0){
	                    		$provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
	                    		$provider->is_document=0;
	                    		$provider->status='DOCUMENT';
	                    		$provider->save();
	                    	}
	                    }
                   }
					}
				} 

			} 
			else if($request->admin_service=='TRANSPORT') {
				ProviderService::where('provider_vehicle_id',$request->id)->update(['ride_delivery_id'=>$request->vehicle_id]);
		   
			}
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
		}
		catch (\Throwable $e) {

			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function updatelanguage(Request $request)
	{
	 
	 $this->validate($request, [
			'language' => 'required',
		]);
		try{
		   $provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
		   $provider->language=$request->language;
		   $provider->save();
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
		}
		 catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}

	}
	
	public function search_provider(Request $request)
	{

		$results=array();
		$term =  $request->input('stext');  
		$queries = Provider::where(function ($query) use($term) {
			$query->where('first_name', 'LIKE', $term.'%')
				->orWhere('last_name', 'LIKE', $term.'%');
			})->take(5)->get();
		foreach ($queries as $query)
		{
			$results[]=$query;
		}
		return response()->json(array('success' => true, 'data'=>$results));

	}

	public function notification(Request $request)
	{
		try{
			$timezone=(Auth::guard('provider')->user()->state_id) ? State::find(Auth::guard('provider')->user()->state_id)->timezone : '';
			
			$jsonResponse = [];
			if($request->has('limit')) {
						$notifications = Notifications::where('company_id', Auth::guard('provider')->user()->company_id)->where('notify_type','!=', "user")->where('status','active')->whereDate('expiry_date','>=',Carbon::today())->orderby('id','desc')->take($request->limit)->offset($request->offset)->get();
			}else{
					$notifications = Notifications::where('company_id', Auth::guard('provider')->user()->company_id)->where('notify_type','!=', "user")->where('status','active')->whereDate('expiry_date','>=',Carbon::today())->orderby('id','desc')->paginate(10);  
			}
			if(count($notifications) > 0){
				foreach($notifications as $k=>$val){
	              $notifications[$k]['created_at']=(Carbon::createFromFormat('Y-m-d H:i:s',$val['created_at'], 'UTC'))->setTimezone($timezone)->format('Y-m-d H:i:s');    
	            } 
           } 
			   $jsonResponse['total_records'] = count($notifications);
			$jsonResponse['notification'] = $notifications;
		}catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
		return Helper::getResponse(['data' => $jsonResponse]);
	}

	public function template(Request $request)
	{
		try{
			$guard=Helper::getGuard();
			if($guard == 'SHOP'){
				$country_id=Auth::guard('shop')->user()->country_id;
				$company_id=Auth::guard('shop')->user()->company_id;
				$id=Auth::guard('shop')->user()->id;
				$type="SHOP";
			}else if($guard == 'PROVIDER'){
				$type="PROVIDER";
				$country_id=Auth::guard('provider')->user()->country_id;
				$company_id=Auth::guard('provider')->user()->company_id;
				$id=Auth::guard('provider')->user()->id;
			} else {
				$type="FLEET";
				$country_id=Auth::guard('admin')->user()->country_id;
				$company_id=Auth::guard('admin')->user()->company_id;
				$id=Auth::guard('admin')->user()->id;
			}		
			\Log::info($id." ". $type." ".$country_id." ".$company_id);
			$countryform = CountryBankForm::with(['bankdetails' => function($query) use($id,$type) {
				$query->where('type_id',$id);
				$query->where('type',$type); 
			}])->where('country_id', $country_id)->where('company_id',$company_id)->get();
			return Helper::getResponse(['data'=>$countryform]);
		}
		catch (\Throwable $e) {
			\Log::info($e);
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function addbankdetails(Request $request)
	{

	  for ($i = 0; $i < count($request->bankform_id); $i++) {
		 $this->validate($request,
		 [
		  'bankform_id.'.$i => 'required',
		  'keyvalue.'.$i => 'required',
		], 
		[
		'keyvalue.'.$i.'.'.'required' => 'Please fill All Details', 
		]);
	   }
	   
	  try{
			
			$guard=Helper::getGuard();
			 if($guard == 'SHOP'){
				 $company_id=Auth::guard('shop')->user()->company_id;
				 $id=Auth::guard('shop')->user()->id;
				 $type="SHOP";
			 }else if($guard == 'PROVIDER'){
				$type="PROVIDER";
			   $company_id=Auth::guard('provider')->user()->company_id;
				$id=Auth::guard('provider')->user()->id;
			 } else {
				 $type="FLEET";
				 $company_id=Auth::guard('admin')->user()->company_id;
				 $id=Auth::guard('admin')->user()->id;

			 }

		   
			for ($i=0; $i < count($request->bankform_id) ; $i++) { 
				$providerbank= new ProviderBankdetail;
				$providerbank->bankform_id=$request->bankform_id[$i];
				$providerbank->keyvalue=$request->keyvalue[$i];
				$providerbank->type_id=$id;
				$providerbank->company_id=$company_id;
				$providerbank->type=$type;
				$providerbank->save();

			}
			 
			if($type=="PROVIDER"){
				$provider= Provider::findOrFail($id);
				$provider->is_bankdetail=1;
				$provider->save();

				(new SendPushNotification)->updateProviderStatus($provider->id, 'provider', trans('admin.document_msgs.document_saved'), 'Account Info', ['service' => $provider->is_service, 'document' => $provider->is_document, 'bank' => $provider->is_bankdetail] );

			}else if($type=="FLEET"){
				$admin= Admin::findOrFail($id);
				$admin->is_bankdetail=1;
				$admin->save();    

			}
			else if($type=="SHOP") {
			 try{     
				$store= \App\Models\Order\Store::findOrFail($id);
				$store->is_bankdetail=1;
				$store->save();
			 }    
			 catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			 }    

			}
			
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

		}
		 catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	
	}


	public function editbankdetails(Request $request)
	{

		for ($i = 0; $i < count($request->bankform_id); $i++) {
			$this->validate($request,
			[
				'bankform_id.'.$i => 'required',
				'keyvalue.'.$i => 'required',
				'id.'.$i => 'required',
			], 
			[
				'keyvalue.'.$i.'.'.'required' => 'Please fill All Details', 
			]);
		}     
	  
		try{
			for ($i=0; $i < count($request->bankform_id) ; $i++) { 
				$providerbank= ProviderBankdetail::findOrFail($request->id[$i]);
				$providerbank->bankform_id=$request->bankform_id[$i];
				$providerbank->keyvalue=$request->keyvalue[$i];
				$providerbank->save();
			}
			
		 return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
		}
		catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function onlinestatus(Request $request,$id)
	{
	   try{
		   if($id > 2  ){
		   return Helper::getResponse(['status' => 422, 'message' => 'Send Valid Status']);
			  }
		   $provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
		   if($provider['status']=='APPROVED'){
			$provider->is_online=$id;
			$provider->save();
			return Helper::getResponse(['status' => 200,"data"=>['provider_status'=>$provider->is_online], 'message' => trans('admin.update')]);
		   }else{

			   return Helper::getResponse(['status' => 422, 'message' => 'Estado não atualizado, contacte à Admin']);

		   }

		}
		 catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	
	}
	//For friend refer status

	public function referemail(Request $request)
	{  
	  try{
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

		}
		 catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function updatelocation(Request $request)
	{  
		$this->validate($request, [
			'latitude' => 'required',
			'longitude' => 'required',
		]);

		try{
			  if(Auth::guard('provider')->user()->is_assigned==1){
			  return Helper::getResponse(['status' => 404, 'message' =>'Provider in Ride ']);    
			  }else{
				  RequestFilter::where("provider_id",Auth::guard('provider')->user()->id)->delete();
			  }
		   Provider::where('id',Auth::guard('provider')->user()->id)->update([
						'latitude' => $request->latitude,
						'longitude' => $request->longitude,
				]);
			
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);

		}
		 catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function totalEarnings(Request $request, $id)
	{
		try{
			$Provider = Provider::findOrFail($id);
			$types = array('1'=>'today','2'=>'week','3'=>'month');
			foreach($types as $key=>$type){
				$ServiceRequest = \App\Models\Service\ServiceRequestPayment::select(DB::Raw('SUM(provider_pay) as salary'))->where('provider_id',$Provider->id);
				
				if($type == 'week'){                
									
					$ServiceRequest = $ServiceRequest->where('updated_at', '>', Carbon::now()->startOfWeek())
									->where('updated_at', '<', Carbon::now()->endOfWeek())->first();
									
				}else if($type == 'month'){
					$ServiceRequest = $ServiceRequest->whereMonth('updated_at', Carbon::now()->month)->first();
				}else{
					$ServiceRequest = $ServiceRequest->whereRaw('Date(updated_at) = CURDATE()')->first();
				}
				$earnings =0;

				if($ServiceRequest != null) $earnings += $ServiceRequest->salary;
                $response[$type] = Helper::currencyFormat($earnings);                
			}
			return Helper::getResponse([ 'message' =>'Provider Earnings','data' => $response]);
		}catch(ModelNotFoundException $e){
			return Helper::getResponse(['status' => 500, 'message' => trans('api.provider.provider_not_found'), 'error' => trans('api.provider.provider_not_found') ]);
		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.provider.provider_not_found'), 'error' => trans('api.provider.provider_not_found') ]);
		}
	}

  
	public function clear(Request $request)
	{  
		$provider = Provider::find($request->provider_id);

		if($provider != null) {
			$provider->is_assigned = 0;
			$provider->is_online = 1;
			$provider->status = 'APPROVED';
			$provider->activation_status = 1;
			$provider->save();
		}

		$userRequests = UserRequest::where('provider_id', $provider->id)->get();

		foreach ($userRequests as $userRequest) {
			$userRequest->delete();

			if($userRequest->admin_service == "TRANSPORT" ) {
				$newRequests = \App\Models\Transport\RideRequest::where('provider_id', $provider->id)->whereNotIn('status', ['SCHEDULED', 'COMPLETED', 'CANCELLED'])->get();
				foreach ($newRequests as $newRequest) {
					$newRequest->delete();
				}
			}

		}

		return response()->json([
			'data' => $provider, 
		]);
	}

	public function get_chat(Request $request) {

		$this->validate($request,[
			'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
			'id' => 'required', 
		]);

		$chat=Chat::where('admin_service', $request->admin_service)->where('request_id', $request->id)->where('company_id', Auth::guard('provider')->user()->company_id)->get();

		return Helper::getResponse(['data' => $chat]);
	}

	public function updateDeviceToken(Request $request){
		$this->validate($request,[
			'device_token' => 'required'
		]);
		$provider = Provider::find( Auth::guard('provider')->user()->id );
		try{
			$company_id = Auth::guard('provider')->user()->company_id;
			$user_id = Auth::guard('provider')->user()->id;
			$update = Provider::where('id',$user_id)->update(['device_token'=>$request->device_token]);
			if($update){
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
			}else{
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			}
		}catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}


    public function addproviderservice(Request $request){    

        $this->validate($request, [

            'admin_service' =>'required|in:TRANSPORT,ORDER,SERVICE',

            'category_id' => 'required',

            'sub_category_id' => 'required',

            'service_id' => 'required',

            ],['service_id.required'=>'Please On Atleast One Service']);
         // dd($request->service_id);
         // \Log::info($request->service_id);
         // \Log::info($request->base_fare);
         // \Log::info($request->per_miles);
         // \Log::info($request->per_mins);
         
           try{

                foreach($request->service_id  as $key =>$val){

                    $providerservice=new  ProviderService;    

                    $providerservice->provider_id=Auth::guard('provider')->user()->id;

                    $providerservice->company_id=Auth::guard('provider')->user()->company_id;

                    $providerservice->admin_service=$request->admin_service;

                    $providerservice->category_id=$request->category_id[$key];

                    $providerservice->service_id=$val;
              
                    if(isset($request->base_fare[$key])){
                    
                        $providerservice->base_fare=$request->base_fare[$key];

                    }
                    if(isset($request->per_miles[$key])){

                        $providerservice->per_miles= isset($request->per_miles[$key]) ? $request->per_miles[$key] : 0.00; 
                    }   
                    if(isset($request->per_mins[$key])){
                        $providerservice->per_mins=$request->per_mins[$key];

                    }

                    $providerservice->sub_category_id=$request->sub_category_id[$key];

                   $providerservice->save();

                   $document = Document::where('company_id', Auth::guard('provider')->user()->company_id)->where('service_category_id',$request->category_id[$key])->get();
                    // dd($document);
                   if(count($document)>0){
	                   	 foreach ($document as $key => $value) {
	                    	$provider_document=ProviderDocument::where('provider_id',Auth::guard('provider')->user()->id)->where('document_id',$value->id)->get();
	                    	if(count($provider_document)==0){
	                    		$provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
	                    		$provider->is_document=0;
	                    		$provider->status='DOCUMENT';
	                    		$provider->save();
	                    	}
	                    }
                   }
                   

                }
               $documents=Document::where('type',$request->admin_service)->where('status',1)->count();
			if($documents>0){
	            $document=Document::whereHas('provider_document', function($query){

	            $query->where('provider_id',Auth::guard('provider')->user()->id);

	                })->where('type',$request->admin_service)->where('status',1)->count();
				$provider= Provider::findOrFail(Auth::guard('provider')->user()->id);
				$provider->is_service=1;
				if($document==0){
					$provider->is_document=0;
					$provider->status='DOCUMENT';

					(new SendPushNotification)->updateProviderStatus($provider->id, 'provider', trans('admin.providers.status_changed'), 'Account Info', ['service' => $provider->is_service, 'document' => $provider->is_document, 'bank' => $provider->is_bankdetail] );
				}
				$provider->save();
		     }

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

           }

            catch (\Throwable $e) {



                return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);

            }

    }

   public function editproviderservice(Request $request)

   {    

        $this->validate($request, [

             'admin_service' =>'required',

            'category_id' => 'required',

            'sub_category_id' => 'required',

            'admin_service' => 'required',

            ],['service_id.required'=>'Please On Atleast One Service']);



        try{ 

        ProviderService::where('admin_service','SERVICE')->where('provider_id',Auth::guard('provider')->user()->id)->delete();    



            foreach($request->service_id  as $key =>$val){

                $providerservice=new  ProviderService;    

                $providerservice->provider_id=Auth::guard('provider')->user()->id;

                $providerservice->company_id=Auth::guard('provider')->user()->company_id;

                $providerservice->admin_service=$request->admin_service;

                $providerservice->category_id=$request->category_id[$key];

                $providerservice->service_id=$val;

                if(isset($request->base_fare[$key])){
                    \Log::info("keytest:".$key);
                    	 \Log::info('provider base  test:'.$request->base_fare[$key]);
                    $providerservice->base_fare=$request->base_fare[$key];

                } 
                if(isset($request->per_miles[$key])){

                    $providerservice->per_miles= isset($request->per_miles[$key]) ? $request->per_miles[$key] : 0.00;    
                }
                if(isset($request->per_mins[$key])){

                    $providerservice->per_mins=$request->per_mins[$key];
                }

                $providerservice->sub_category_id=$request->sub_category_id[$key];

                $providerservice->save();

            }

           

            $provider= Provider::findOrFail(Auth::guard('provider')->user()->id);

            $provider->is_service=1;

            $provider->save();

           return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

        }

        catch (\Throwable $e) {



            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);

        }

   } 

}
