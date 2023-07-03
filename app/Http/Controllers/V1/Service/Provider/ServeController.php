<?php

namespace App\Http\Controllers\V1\Service\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use App\Models\Common\RequestFilter;
use App\Models\Service\ServiceCategory; 
use App\Models\Service\ServiceSubcategory;
use App\Models\Service\Service;
use App\Models\Service\ServiceCityPrice;
use App\Models\Service\ServiceRequest;
use App\Models\Service\ServiceRequestPayment;
use App\Models\Service\ServiceRequestDispute;
use App\Services\SendPushNotification;
use App\Models\Common\ProviderService;
use Illuminate\Support\Facades\Hash;
use App\Services\ReferralResource;
use App\Models\Common\Provider;
use App\Models\Service\ServiceCancelProvider;
use Location\Distance\Vincenty;
use Location\Coordinate;
use App\Models\Common\Setting;
use App\Models\Common\Reason;
use App\Models\Common\Rating;
use App\Models\Common\UserRequest;
use App\Models\Common\AdminService;
use App\Models\Common\User;
use App\Models\Common\Promocode;
use App\Models\Common\PromocodeUsage;
use App\Models\Common\PeakHour;
use App\Models\Common\Dispute;
use App\Traits\Actions;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Services\Transactions;
use App\Models\Common\Admin;
use Auth;
use DB;
use Log;
use App\Services\V1\Common\ProviderServices;

class ServeController extends Controller 
{
    use Actions;
    private $model;
    private $request;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function __construct(Service $model)
    {
        $this->model = $model;
    }*/
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
	{
		try{
			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));

	        $siteConfig = $settings->site;
			$serviceConfig = $settings->service;
            
			if(!empty($request->latitude)) {
				Provider::where('id', Auth::guard('provider')->user()->id)->update([
						'latitude' => $request->latitude,
						'longitude' => $request->longitude,
				]);

				//when the provider is idle for a long time in the mobile app, it will change its status to hold. If it is waked up while new incoming request, here the status will change to active
				//DB::table('provider_services')->where('provider_id',$Provider->id)->where('status','hold')->update(['status' =>'active']);
			}
			$Provider = Provider::with(['service'  => function($query) {  
				$query->where('admin_service', 'SERVICE' ); 
			}])->where('id', Auth::guard('provider')->user()->id)->first();

			$provider = $Provider->id;

			$IncomingRequests = ServiceRequest::with(['user', 'payment','service', 'chat'])
				->where('provider_id', $provider)
				->where('status','<>', 'CANCELLED')
				->where('status','<>', 'SCHEDULED')
				->where('provider_rated', '0')
				->where('provider_id', $provider )->first();

			$Reason=Reason::where('type','PROVIDER')->get();

			$referral_total_count = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_count;
			$referral_total_amount = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_amount;

			if($IncomingRequests != null){
				$categoryId = $IncomingRequests->service->service_category_id;
				$subCategoryId = $IncomingRequests->service->service_subcategory_id;
				$IncomingRequests->category = ServiceCategory::where('id',$categoryId)->first();
				$IncomingRequests->subcategory = ServiceSubCategory::where('id',$subCategoryId)->first();
				if($IncomingRequests != null){
					if($IncomingRequests->payment != null){
						$IncomingRequests->promo_code = Promocode::where('id',$IncomingRequests->payment->promocode_id)->first();
					}else{
						$IncomingRequests->promo_code = null;
					}
				}
				$Provider_service=ProviderService::where('provider_id',$IncomingRequests->provider_id)->where('service_id',$IncomingRequests->service_id)->first();
				$cityPriceList = ServiceCityPrice::where(['service_id'=>$IncomingRequests->service_id, 'city_id'=> $IncomingRequests->city_id])->first();
			}		

			$Response = [
					'account_status' => $Provider->status,
					'service_status' => !empty($IncomingRequests) ? 'SERVICE':'ACTIVE',
					'FareType' => isset($cityPriceList->fare_type)?$cityPriceList->fare_type:'FIXED',
					'requests' => $IncomingRequests,
					'provider_details' => $Provider,
					'reasons' => $Reason,/*
					'waitingStatus' => (count($IncomingRequests) > 0) ? $this->waiting_status($IncomingRequests[0]->request_id) : 0,
					'waitingTime' => (count($IncomingRequests) > 0) ? $this->total_waiting($IncomingRequests[0]->request_id) : 0,*/
					'referral_count' => $siteConfig->referral_count,
					'referral_amount' => $siteConfig->referral_amount,
					'serve_otp' => 0,
					'referral_total_count' => $referral_total_count,
					'referral_total_amount' => $referral_total_amount,
				];

			if($IncomingRequests != null){
				if(!empty($request->latitude) && !empty($request->longitude)) {
					// $distance = $this->calculate_distance($request,$IncomingRequests->id);
				}	
			}

			return Helper::getResponse(['data' => $Response ]);

		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function calculate_distance($request, $id)
	{
		
		$this->validate($request, [
				'latitude' => 'required|numeric',
				'longitude' => 'required|numeric'
			]);
		try{
			$Provider = Auth::guard('provider')->user();
			$serveRequest = ServiceRequest::where('status','PICKEDUP')
							->where('provider_id',$Provider->id)
							->find($id);
			if($serveRequest != null && ($request->latitude !='' && $request->longitude !='')){

				Log::info("REQUEST ID:".$serveRequest->id."==SOURCE LATITUDE:".$serveRequest->track_latitude."==SOURCE LONGITUDE:".$serveRequest->track_longitude);
			
				if($serveRequest->track_latitude != '' && $serveRequest->track_longitude != ''){

					$coordinate1 = new Coordinate($serveRequest->track_latitude, $serveRequest->track_longitude); /** Set Distance Calculation Source Coordinates ****/
					$coordinate2 = new Coordinate($request->latitude, $request->longitude); /** Set Distance calculation Destination Coordinates ****/

					$calculator = new Vincenty();

					/***Distance between two coordinates using spherical algorithm (library as mjaschen/phpgeo) ***/ 

					$mydistance = $calculator->getDistance($coordinate1, $coordinate2); 

					$meters = round($mydistance);

					Log::info("REQUEST ID:".$serveRequest->id."==BETWEEN TWO COORDINATES DISTANCE:".$meters." (m)");

					if($meters >= 100){
						/*** If traveled distance riched houndred meters means to be the source coordinates ***/
						$traveldistance = round(($meters/1000),8);

						$calulatedistance = $serveRequest->track_distance + $traveldistance;

						$serveRequest->track_distance  = $calulatedistance;
						$serveRequest->distance        = $calulatedistance;
						$serveRequest->track_latitude  = $request->latitude;
						$serveRequest->track_longitude = $request->longitude;
						$serveRequest->save();
					}
				}else if(!$serveRequest->track_latitude && !$serveRequest->track_longitude) {
					$serveRequest->distance             = 0;
					$serveRequest->track_latitude      = $request->latitude;
					$serveRequest->track_longitude     = $request->longitude;
					$serveRequest->save();
				}
			}
			return $serveRequest;
		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function updateServe(Request $request)
	{
		$this->validate($request, [
		  	'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
	   	]);
		try{
			$setting = Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first();
			$settings = json_decode(json_encode($setting->settings_data));

	        $siteConfig = $settings->site;
			$serviceConfig = $settings->service;
			$serveRequest = ServiceRequest::with('user','service','service.serviceCategory')->findOrFail($request->id);

			$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'SERVICE' )->first();
			if($request->status == 'PAYMENT' && $serveRequest->payment_mode != 'CASH') {
				$serveRequest->status = 'COMPLETED';
				$serveRequest->paid = 0;

				(new SendPushNotification)->serviceProviderComplete($serveRequest, 'service', 'Service Completed');
			} else if ($request->status == 'PAYMENT' && $serveRequest->payment_mode == 'CASH') {
				
				if($serveRequest->status=='COMPLETED'){
					//for off cross clicking on change payment issue on mobile
					return Helper::getResponse(['data' => $serveRequest ]);
				}				
				$serveRequest->status = 'COMPLETED';
				$serveRequest->paid = 1;
				(new SendPushNotification)->serviceProviderComplete($serveRequest, 'service', 'Service Completed');
				//for completed payments
				$RequestPayment = ServiceRequestPayment::where('service_request_id', $request->id)->first();
				$RequestPayment->payment_mode = 'CASH';
				$RequestPayment->cash = $RequestPayment->payable;
				$RequestPayment->payable = 0;             
				$RequestPayment->save();               

			} else {
				$serveRequest->status = $request->status;
				if($request->status == 'ARRIVED'){
					(new SendPushNotification)->serviceProviderArrived($serveRequest, 'service', 'Service Arrived');
				}
			}

			if($request->status == 'PICKEDUP'){
				$serveRequest->started_at = (Carbon::now())->toDateTimeString();
				if(isset($request->otp) && (isset($serviceConfig->serve_otp) && $serviceConfig->serve_otp == 1)){
					if($request->otp == $serveRequest->otp){
						if($request->hasFile('before_picture')) {
							$serveRequest->before_image = Helper::upload_providerfile($request->file('before_picture'), 'xuber/requests', 'srbi-before-'.time().'.png');
						}
						if($serveRequest->is_track == "YES"){
							$serveRequest->distance  = 0; 
						}					
						(new SendPushNotification)->serviceProviderPickedup($serveRequest, 'service', 'Service Started');
					}else{
						return Helper::getResponse(['status' => 500, 'message' => trans('api.otp'), 'error' => trans('api.otp') ]);
				   	}
				}else{
					if($request->hasFile('before_picture')) {
						$serveRequest->before_image = Helper::upload_providerfile($request->file('before_picture'), 'xuber/requests', 'srbi-before-'.time().'.png');
					}
					if($serveRequest->is_track == "YES"){
						$serveRequest->distance  = 0; 
					}
					(new SendPushNotification)->serviceProviderPickedup($serveRequest, 'service', 'Service Started');
				}
			}
			
			
			if($request->status == 'DROPPED') {
				$extracharges = isset($request->extra_charge)&& $request->extra_charge!='' ?$request->extra_charge:0;
				$extracharges_notes = isset($request->extra_charge_notes)&& $request->extra_charge_notes!='' ?$request->extra_charge_notes:0;
				$serveRequest->finished_at = (Carbon::now())->toDateTimeString();
				$StartedDate  = date_create($serveRequest->started_at);
				$FinisedDate  = Carbon::now();
				$TimeInterval = date_diff($StartedDate,$FinisedDate);
				$MintuesTime  = $TimeInterval->i;
				$serveRequest->travel_time = $MintuesTime;

				if($request->hasFile('after_picture')) {
					$serveRequest->after_image = Helper::upload_providerfile($request->file('after_picture'), 'xuber/requests', 'srbi-after-'.time().'.png');
				}
				$distance = isset($request->distance) ?$request->distance :0;
				$serveRequest->save();
				$serveRequest->with('user')->findOrFail($request->id);
				$getInvoice = $this->invoice('SERVICE',$request->id,$extracharges,$extracharges_notes,$serveRequest->distance);
			   
				(new SendPushNotification)->serviceProviderDropped($serveRequest, 'service', 'Service Dropped');

			}
			if($request->status == 'PAYMENT') {
				
				$serveRequest->save();
				$serveRequest->with('user')->findOrFail($request->id);
				
				(new SendPushNotification)->serviceProviderConfirmPay($serveRequest, 'service', 'Confirm Payment');

			}
			$serveRequest->save();
			if($user_request != null){
				$user_request->provider_id = $serveRequest->provider_id;
				$user_request->status = $serveRequest->status;
				$user_request->request_data = json_encode($serveRequest);

				$user_request->save();
			}
			//for completed payments
			$serveRequestResponse = ServiceRequest::with('user','payment','service')->findOrFail($serveRequest->id);
			//$this->callTransaction($id);
			if($serveRequestResponse != null){
				if($serveRequestResponse->payment != null){
					$serveRequestResponse->promo_code = Promocode::where('id',$serveRequestResponse->payment->promocode_id)->first();
				}else{
					$serveRequestResponse->promo_code = null;
				}
			}
			//Send message to socket
			$requestData = ['type' => 'SERVICE', 'room' => 'room_'.Auth::guard('provider')->user()->company_id, 'id' => $serveRequest->id, 'city' => ($setting->demo_mode == 0) ? $serveRequest->city_id : 0, 'user' => $serveRequest->user_id ];

			app('redis')->publish('checkServiceRequest', json_encode( $requestData ));
	   			
	   		//for create the transaction
            (new ServeController)->callTransaction($request->id);	

			return Helper::getResponse(['data' => $serveRequestResponse ]);

		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.unable_accept'), 'error' => $e->getMessage() ]);
		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.connection_err'), 'error' => $e->getMessage() ]);
		}
	}

	public function invoice($admin_service, $request_id,$extracharges,$extracharges_notes,$userdistance ='') 
    {
        try {

            $UserRequest = ServiceRequest::findOrFail($request_id);
			$cityId = $UserRequest->city_id;
			$serviceId = $UserRequest->service_id;
			$companyId = $UserRequest->company_id;
			$providerId = $UserRequest->provider_id;
			$distance = $userdistance != 0 ? $userdistance :$UserRequest->distance;
		    $serviceDetails=Service::with('serviceCategory')->where('id',$serviceId)->where('company_id',$companyId)->first();
            $cityPriceList = ServiceCityPrice::where(['service_id'=>$serviceId, 'city_id'=> $cityId])->first();
			$baseFare =0;
			$perMiles =0;
			$perMins =0;
			if($cityPriceList != null){
				$fareType = $cityPriceList->fare_type;
				$getbaseFare = $cityPriceList->base_fare;
				$getperMins = $cityPriceList->per_mins;
				$baseDistance = $cityPriceList->base_distance;
				$getperMiles = $cityPriceList->per_miles;
				$commissionPercent = $cityPriceList->commission;
				$taxPercent = $cityPriceList->tax;
				$fleetPercent = $cityPriceList->fleet_commission;
			}else{
				$fareType = 'FIXED';
				$getbaseFare = 0;
				$getperMins = 0;
				$baseDistance = 0;
				$getperMiles = 0;
				$commissionPercent = 0;
				$taxPercent = 0;
				$fleetPercent = 0;
				$price_choose = '';
			}
			$provider_service = ProviderService::where(['provider_id'=>$providerId,'admin_service'=>$admin_service,'service_id'=>$serviceId])->first();
			if($serviceDetails->serviceCategory->price_choose=='admin_price'){
				if(!empty($UserRequest->quantity))
					$baseFare=Helper::decimalRoundOff($getbaseFare*$UserRequest->quantity);
				else
					$baseFare=Helper::decimalRoundOff($getbaseFare);

				$perMiles=Helper::decimalRoundOff($getperMiles);
				$perMins=round($getperMins,2);
			}
			else{
				if($provider_service != null){
					if(!empty($UserRequest->quantity) && $UserRequest->quantity != 0)
						$baseFare=Helper::decimalRoundOff($provider_service->base_fare*$UserRequest->quantity);
					else
						$baseFare=Helper::decimalRoundOff($provider_service->base_fare);

					$perMiles=Helper::decimalRoundOff($provider_service->per_miles);
					$perMins=Helper::decimalRoundOff($provider_service->per_mins);
				}
			}

			$price_choose = $serviceDetails->serviceCategory->price_choose;

			$to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $UserRequest->finished_at);
			$from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $UserRequest->started_at);
			$diff_in_minutes = $to->diffInMinutes($from);
			if($fareType == 'HOURLY'){
				$fareAmount = $baseFare + ($perMins * $diff_in_minutes);
				$minsAmount = ($perMins * $diff_in_minutes);
				$distanceAmount = 0;				
			}elseif($fareType == 'DISTANCETIME'){
				$minsAmount = $perMins * $diff_in_minutes;
				$distanceAmount = 0;
				if($baseDistance >= $distance){
					$fareAmount = $baseFare + $minsAmount;
				}else{
					$distanceAmount = abs($distance-$baseDistance) * $perMiles;
					$fareAmount = $baseFare + $distanceAmount + $minsAmount;
				}				
			}else{
				// FIXED PRICE TYPE
				$fareAmount = $baseFare;
				$distanceAmount = $minsAmount = 0;	
			}  
			$promoId = $UserRequest->promocode_id != 0 ? $UserRequest->promocode_id:0;
			$Discount = 0; 
            $discount_per=0;
            $Wallet = 0;			
		    $commissionAmount = ($fareAmount + $extracharges) * ($commissionPercent / 100);
			$Fixed = $fareAmount + $commissionAmount;


			
			$taxAmount = $Fixed * ($taxPercent / 100);
			$Total = ($Fixed + $extracharges + $taxAmount);
			// Promo Code discounts should be added here.
            // if($PromocodeUsage = PromocodeUsage::where('user_id',$UserRequest->user_id)->where('status','ADDED')->first()){
            //     if($Promocode = Promocode::find($PromocodeUsage->promocode_id)){
            //         $Discount = $Promocode->discount;
            //         $PromocodeUsage->status ='USED';
            //         $PromocodeUsage->save();
            //     }
            // }

            	if($promoId>0){
					if($Promocode = Promocode::find($UserRequest->promocode_id)){
						$max_amount = $Promocode->max_amount;
						$discount_per = $Promocode->percentage;
						$discount_amount = ($Total * ($discount_per/100));
	                    if($discount_amount>$Promocode->max_amount){
							$Discount = $Promocode->max_amount;
						}
						else{
							$Discount = $discount_amount;
						}

						$PromocodeUsage = new PromocodeUsage;
						$PromocodeUsage->user_id =$UserRequest->user_id;
						$PromocodeUsage->company_id =Auth::guard('provider')->user()->company_id;
						$PromocodeUsage->promocode_id =$UserRequest->promocode_id;
						$PromocodeUsage->status ='USED';
						$PromocodeUsage->save();


					}                
				}
			$Payamount = $Total-$Discount;
			$ProviderPay = (($Total+$Discount) - $commissionAmount)-$taxAmount;
			
 			$Payment = new ServiceRequestPayment;
 			if(!empty($UserRequest->admin_id)){

				$Fleet = Admin::where('id',$UserRequest->admin_id)->where('type','FLEET')->where('company_id',Auth::guard('provider')->user()->company_id)->first();

				$fleet_per=0;

				if(!empty($Fleet)){
					if(!empty($commissionAmount)){										
						$fleet_per=$Fleet->commision ? $Fleet->commision : 0;
					}
					else{
						$fleet_per=$cityPriceList->fleet_commission ? $cityPriceList->fleet_commission :0;
					}

					$Payment->fleet_id=$UserRequest->admin_id;
					$Payment->fleet_percent=$fleet_per;
				}
			}
			
             
			
            
            // $Total += $Tax;

            if($Total < 0){
                $Total = 0.00; // prevent from negative value
            }
            $currencySymbol = $UserRequest->currency;			
			$Payment->user_id = $UserRequest->user_id;
			$Payment->provider_id = $UserRequest->provider_id;
			$Payment->service_request_id = $UserRequest->id;
			$Payment->company_id = $UserRequest->company_id;
			$Payment->payment_mode = $UserRequest->payment_mode;
			$Payment->fixed = ($baseFare+$commissionAmount);
			$Payment->mins_fare = $minsAmount;
			$Payment->distance_fare = $distanceAmount;
            $Payment->provider_pay = $ProviderPay;
            $Payment->minute = $diff_in_minutes;
			$Payment->commision = $commissionAmount;
			$Payment->commision_percent = $commissionPercent;
			$Payment->tax = $taxAmount;
			$Payment->tax_percent = $taxPercent;
			$Payment->total = $Total;
			$Payment->extra_charges = $extracharges;
			$Payment->extra_charges_notes = $extracharges_notes;
			if($promoId != 0){
				$Payment->promocode_id = $promoId;
			}
            if($Discount != 0 && $PromocodeUsage){
                $Payment->promocode_id = $PromocodeUsage->promocode_id;
			}
            $Payment->discount = $Discount;
            $Payment->discount_percent = $discount_per;
            if($UserRequest->use_wallet == 1 && $Total > 0){
				$User = User::find($UserRequest->user_id);
                $Wallet = $User->wallet_balance;
                if($Wallet != 0 && $Wallet != 0.00 ){
                    if($Payamount > $Wallet){
                        $Payment->wallet = $Wallet;
						$Payable = $Payamount - $Wallet; 						                     
						$Payment->total = abs($Total);
						$Payment->payable = abs($Payable);
						$Payment->is_partial=1;

						if($UserRequest->payment_mode == 'CASH'){
							$Payment->round_of = round($Payable)-abs($Payable);
							$Payment->total = abs($Total);
							$Payment->payable = round($Payable);
						}
						
						// charged wallet money push 
						// (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,$Wallet);
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,Helper::currencyFormat($Wallet,$currencySymbol), 'service', 'Wallet Info');

                        $transaction['amount']=$Wallet;
						$transaction['id']=$UserRequest->user_id;
						$transaction['transaction_id']=$UserRequest->id;
						$transaction['transaction_alias']=$UserRequest->booking_id;
						$transaction['company_id']=$UserRequest->company_id;
						$transaction['transaction_msg']='service deduction';

						(new Transactions)->userCreditDebit($transaction,0);

                    }else{
                        $WalletBalance = $Wallet - $Total;                        
						$Payment->wallet = $Payamount;
						$Payment->payable = abs($Payamount);
                        //update user request table
                        $UserRequest->paid = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();
						// charged wallet money push 
						// (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,$Total);
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,Helper::currencyFormat($Payamount,$currencySymbol), 'service', 'Wallet Info');

                        $transaction['amount']=$Payamount;
						$transaction['id']=$UserRequest->user_id;
						$transaction['transaction_id']=$UserRequest->id;
						$transaction['transaction_alias']=$UserRequest->booking_id;
						$transaction['company_id']=$UserRequest->company_id;
						$transaction['transaction_msg']='service deduction';

						(new Transactions)->userCreditDebit($transaction,0);
                    }
                }
            }else{
            	if($UserRequest->payment_mode == 'CASH'){
					$Payment->round_of = round($Payamount)-abs($Payamount);
					$Payment->total = abs($Total);
					$Payment->payable = round($Payamount);
				} else {
					$Payment->total = abs($Total);
					$Payment->payable = abs($Payamount);
				}
				
            }

            $Payment->tax = $taxAmount;
            $Payment->save();
            
           // dd($Payment);   
            return $Payment;

        } catch (ModelNotFoundException $e) {
            return false;
        }
	}
	
	public function cancelServe(Request $request)
	{
		$setting = Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first();
		$settings = json_decode(json_encode($setting->settings_data));

        $siteConfig = $settings->site;
        $transportConfig = $settings->service;
		$serviceRequest = ServiceRequest::findOrFail($request->id);

		$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'SERVICE' )->first();

		$admin_service = AdminService::where('admin_service', 'SERVICE')->where('company_id', Auth::guard('provider')->user()->company_id)->first();
		$serviceDelete = RequestFilter::where('admin_service' , 'SERVICE')->where('request_id', $user_request->id)->first();
		if(!empty($user_request)){

			if($serviceDelete != null) {
				$serviceDelete->delete();
				$user_request->delete();
			}
			if($serviceRequest != null) {
				$cancelreason = isset($request->reason)?$request->reason:'cancelled';
				ServiceRequest::where('id', $serviceRequest->id)->update(['status' => 'CANCELLED','cancelled_by'=>'PROVIDER','cancel_reason'=>$cancelreason]);
				//ProviderService::where('provider_id',$serviceRequest->provider_id)->update(['status' => 'active']);
				Provider::where('id', $serviceRequest->provider_id)->update(['is_assigned' => 0]);
			
				/*$service_cancel_provider = new ServiceCancelProvider;
				$service_cancel_provider->company_id = Auth::guard('provider')->user()->company_id;;
				$service_cancel_provider->user_id = Auth::guard('provider')->user()->id;;
				$service_cancel_provider->provider_id = $serviceRequest->provider_id;
				$service_cancel_provider->service_id = $serviceRequest->service_id;
				$service_cancel_provider->save();*/
			}
	    }
		//Send message to socket
		$requestData = ['type' => 'SERVICE', 'room' => 'room_'.Auth::guard('provider')->user()->company_id, 'id' => $serviceRequest->id, 'user' => $serviceRequest->user_id ];
		app('redis')->publish('checkServiceRequest', json_encode( $requestData ));
		
		return Helper::getResponse(['message' => trans('api.service.request_rejected') ]);
			 // FOR SCHEDULE RIDE => BROADCAST TO MULTIPLE PROVIDERS
		// try {
		// 	//  if($transportConfig->broadcast_request == 1){
		// 	//  	return Helper::getResponse(['message' => trans('api.service.request_rejected') ]);
		// 	//  }else{
		// 	// 	 (new \App\Http\Controllers\Service\Provider\HomeController)->assign_next_provider($serviceRequest->id);
		// 	// 	 return Helper::getResponse(['data' => $serviceRequest->with('user')->get() ]);
		// 	//  }			
		// } catch (ModelNotFoundException $e) {
		//  	return Helper::getResponse(['status' => 500, 'message' => trans('api.unable_accept'), 'error' => $e->getMessage() ]);
		// } catch (Exception $e) {
		//  	return Helper::getResponse(['status' => 500, 'message' => trans('api.connection_err'), 'error' => $e->getMessage() ]);
		// }
	}

	public function rate(Request $request)
    {
			
        $this->validate($request, [
				'rating' => 'required|integer|in:1,2,3,4,5',
				'comment' => 'max:255',
            ],['comment.max'=>'character limit should not exceed 255']);   
		try {
			$serviceRequestid = $request->id;
			$serviceRequest = ServiceRequest::where('id', $serviceRequestid)
					// ->where('status', 'COMPLETED')
					->first();
			if($serviceRequest != null){
				$paymode = isset($serviceRequest->payment_mode)?$serviceRequest->payment_mode:'';
				$requestStatus = isset($serviceRequest->status)?$serviceRequest->status:'';
				if(($paymode == 'CASH' && $requestStatus == 'COMPLETED') || ($paymode != 'CASH' && $requestStatus == 'DROPPED')|| ($paymode != 'CASH' && $requestStatus == 'COMPLETED'))
				{
					$ratingRequest = Rating::where('request_id', $serviceRequestid)
					->where('admin_service', 'SERVICE' )->first();
					\Log::info(['request' => $request->all(), 'ratingRequest' => $ratingRequest]);

					if($ratingRequest == null) {
							Rating::create([
								'company_id' => Auth::guard('provider')->user()->company_id,
								'admin_service' => 'SERVICE',
								'provider_id' => $serviceRequest->provider_id,
								'user_id' => $serviceRequest->user_id,
								'request_id' => $serviceRequest->id,
								'user_rating' => $request->rating,
								'user_comment' => $request->comment,]);
					} else {
						Rating::where('request_id', $serviceRequestid)->where('admin_service', 'SERVICE' )->update([
										'user_rating' => $request->rating,
										'user_comment' => $request->comment,
								]);
					}
					$serviceRequest->provider_rated = 1;
					$serviceRequest->save();
					// Delete from filter so that it doesn't show up in status checks.
					$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'SERVICE' )->first();
					if($user_request != null){
						RequestFilter::where('request_id', $user_request->id)->delete();
						$user_request->delete();
					}
					$provider = Provider::find($serviceRequest->provider_id);
					// Send Push Notification to Provider 
					$average = Rating::where('provider_id', $serviceRequest->provider_id)->avg('provider_rating');

					$provider->is_assigned=0;
					$provider->save();

					$serviceRequest->user->update(['rating' => $average]);

					// (new SendPushNotification)->Rate($serviceRequest, 'service', 'Service Rated');

					return Helper::getResponse(['message' => trans('api.service.request_completed') ]);
				}else{
					return Helper::getResponse(['status' => 500, 'message' => trans('api.service.request_inprogress'), 'error' =>trans('api.service.request_inprogress') ]);
				}	
			}else{
				return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.no_service_found'), 'error' =>trans('api.ride.no_service_found') ]);
			}
		} catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'message' => trans('api.service.request_not_completed'), 'error' =>trans('api.service.request_not_completed') ]);
		}
	}
	 
	 /**
	 * Get the service history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function historyList(Request $request)
	{
		try{
			$jsonResponse = [];
			$jsonResponse['type'] = 'service';
			$request->request->add(['admin_service'=>'Service']);
			$withCallback = array('payment'=>function($query){
				$query->select('id','service_request_id','total','round_of','cash','card','payment_mode','payable', 'mins_fare', 'hour');
			},'service'=>function($query){
				$query->select('id','service_category_id','service_name');
			},'user'=>function($query){
				$query->select('id','first_name','last_name','rating','currency_symbol');
			},'rating');
			$ProviderRequests = ServiceRequest::select('id','booking_id','user_id','provider_id','service_id','allow_description','company_id','s_address','started_at','status','assigned_at','timezone','created_at','schedule_at');


			$data=(new ProviderServices())->providerHistory($request,$ProviderRequests,$withCallback);				
			$jsonResponse['total_records'] = count($data);
			
			$jsonResponse['service'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	/**
	 * Get the service history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getServiceHistorydetails(Request $request,$id)
	{
		try{
            $jsonResponse = [];
			$jsonResponse['type'] = 'service';
			$providerrequest = ServiceRequest::with(array('payment'=>function($query){
				$query->select('id','service_request_id','total','round_of','payment_mode','fixed','tax','minute','extra_charges','total','tips','payable','wallet','discount','cash','card','commision', 'mins_fare','hour');
			},'service'=>function($query){
				$query->select('id','service_category_id','service_name');
			},'service.serviceCategory'=>function($query){
				$query->select('id','service_category_name');
			},'serviceCategory'=>function($query){
				$query->select('id','service_category_name');
			},'user'=>function($query){
				$query->select('id','first_name','last_name','rating','picture','mobile','currency_symbol');
			},'dispute'=>function($query){
				$query->where('dispute_type','provider');
			},'rating'=>function($query){
				$query->where('admin_service','SERVICE');
			}))
			->select('id','booking_id','user_id','allow_description','provider_id','service_id','company_id','before_image','after_image','currency','s_address','started_at','status','timezone');
			$request->request->add(['admin_service'=>'SERVICE','id'=>$id]);
			$data=(new ProviderServices())->providerTripsDetails($request,$providerrequest);
			$jsonResponse['service'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}

	//Save the dispute details
	public function saveServiceRequestDispute(Request $request) {
		$this->validate($request, [
				'id' => 'required', 
				'user_id' => 'required',
				'provider_id'=>'required',
				'dispute_name' => 'required',
				'dispute_type' => 'required',
			]);
		$service_request_dispute = ServiceRequestDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('service_request_id',$request->id)
								->where('dispute_type','provider')
								->first();
	    $request->request->add(['admin_service'=>'SERVICE']);								
		if($service_request_dispute==null)
		{
			
			try{
				$disputeRequest = new ServiceRequestDispute;
				$data=(new ProviderServices())->providerDisputeCreate($request, $disputeRequest);
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
			} 
			catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			}
		}else{
			return Helper::getResponse(['status' => 404, 'message' => trans('Already Dispute Created for the Service Request')]);
		}
	}
	public function getServiceRequestDispute(Request $request,$id) {
		$ride_request_dispute = ServiceRequestDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('service_request_id',$id)
								->where('dispute_type','provider')
								->first();
		return Helper::getResponse(['data' => $ride_request_dispute]);
	}
	public function getdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name','service')->where('service','SERVICE')->where('dispute_type','provider')->get();
        return Helper::getResponse(['data' => $dispute]);
	}

	public function callTransaction($request_id){

		$UserRequest = ServiceRequest::with('provider')->with('payment')->findOrFail($request_id);
		
		if($UserRequest->paid==1){
			$transation=array();
			$transation['admin_service']='SERVICE';
			$transation['company_id']=$UserRequest->company_id;
			$transation['transaction_id']=$UserRequest->id;
			$transation['country_id']=$UserRequest->country_id;
        	$transation['transaction_alias']=$UserRequest->booking_id;		

			$paymentsRequest = ServiceRequestPayment::where('service_request_id',$request_id)->first();

			$provider = Provider::where('id',$paymentsRequest->provider_id)->first();

			$fleet_amount=$discount=$admin_commision=$credit_amount=$balance_provider_credit=$provider_credit=0;                

			if($paymentsRequest->is_partial==1){
				//partial payment
				if($paymentsRequest->payment_mode=="CASH"){
					$credit_amount=$paymentsRequest->wallet + $paymentsRequest->tips;
				}
				else{
					$credit_amount=$paymentsRequest->total + $paymentsRequest->tips;
				}
			}
			else{
				if($paymentsRequest->payment_mode=="CARD" || $paymentsRequest->payment_id=="WALLET"){
					$credit_amount=$paymentsRequest->total + $paymentsRequest->tips;
				}
				else{

					$credit_amount=0;                    
				}    
			}                
			

			//admin,fleet,provider calculations
			if(!empty($paymentsRequest->commision)){

				$admin_commision=$paymentsRequest->commision;

				if(!empty($paymentsRequest->fleet_id)){
					//get the percentage of fleet owners
					$fleet_per=$paymentsRequest->fleet_percent;
					$fleet_amount=($admin_commision) * ( $fleet_per/100 );
					$admin_commision=$admin_commision;

				}
				
				//check the user applied discount
				if(!empty($paymentsRequest->discount)){
					$balance_provider_credit=$paymentsRequest->discount;
				}  

			}
			else{

				if(!empty($paymentsRequest->fleet_id)){
					$fleet_per=$paymentsRequest->fleet_percent;
					$fleet_amount=($paymentsRequest->total) * ( $fleet_per/100 );
					$admin_commision=$fleet_amount;
				}
				if(!empty($paymentsRequest->discount)){
					$balance_provider_credit=$paymentsRequest->discount;
				}    
			}                

			if(!empty($admin_commision)){
				//add the commission amount to admin wallet and debit amount to provider wallet, update the provider wallet amount to provider table				
        		$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$admin_commision;
			   (new Transactions)->adminCommission($transation);
			}

			if(!empty($paymentsRequest->fleet_id) && !empty($fleet_amount)){
				$paymentsRequest->fleet=$fleet_amount;
				$paymentsRequest->save();
				//create the amount to fleet account and deduct the amount to admin wallet, update the fleet wallet amount to fleet table				
        		$transation['id']=$paymentsRequest->fleet_id;
        		$transation['amount']=$fleet_amount;
			   	(new Transactions)->fleetCommission($transation);
				                       
			}
			if(!empty($balance_provider_credit)){
				//debit the amount to admin wallet and add the amount to provider wallet, update the provider wallet amount to provider table				
        		$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$balance_provider_credit;
			   	(new Transactions)->providerDiscountCredit($transation);				
			}

			if(!empty($paymentsRequest->tax)){
				//debit the amount to provider wallet and add the amount to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$paymentsRequest->tax;
				(new Transactions)->taxCredit($transation);
			}			  
			
			if($credit_amount>0){               
				//provider ride amount
				//check whether provider have any negative wallet balance if its deduct the amount from its credit.
				//if its negative wallet balance grater of its credit amount then deduct credit-wallet balance and update the negative amount to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
				$transation['amount']=$credit_amount;

				if($provider->wallet_balance>0){
					$transation['admin_amount']=$credit_amount-($admin_commision+$paymentsRequest->tax);

				}
				else{
					$transation['admin_amount']=$credit_amount-($admin_commision+$paymentsRequest->tax)+($provider->wallet_balance);
				}

				(new Transactions)->providerRideCredit($transation);
			}

			return true;
		}
		else{
			
			return true;
		}
		
	}
}
