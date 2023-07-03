<?php 

namespace App\Services\V1\Transport;

use Illuminate\Http\Request;
use Validator;
use Exception;
use DateTime;
use Carbon\Carbon;
use Auth;
use Lang;
use App\Helpers\Helper;
use GuzzleHttp\Client;
use App\Models\Common\Card;
use App\Models\Common\User;
use App\Models\Common\State;
use App\Models\Common\Admin;
use App\Models\Transport\RideRequest;
use App\Models\Common\CompanyCountry;
use App\Models\Transport\RideRequestPayment;
use App\Models\Transport\RideRequestWaitingTime;
use App\Models\Transport\RideCityPrice;
use App\Models\Transport\RidePeakPrice;
use App\Services\SendPushNotification;
use App\Models\Common\PromocodeUsage;
use App\Models\Common\Promocode;
use App\Models\Common\PeakHour;
use App\Models\Common\Setting;
use App\Services\Transactions;
use App\Services\V1\Common\UserServices;
use App\Models\Common\UserRequest;
use App\Models\Common\GeoFence;
use App\Models\Common\Chat;
use App\Traits\Actions;
use Illuminate\Support\Facades\Mail;


class Ride { 

	use Actions;

	/**
		* Get a validator for a tradepost.
		*
		* @param  array $data
		* @return \Illuminate\Contracts\Validation\Validator
	*/
	protected function validator(array $data) {
		$rules = [
			'location'  => 'required',
		];

		$messages = [
			'location.required' => 'Location Required!',
		];

		return Validator::make($data,$rules,$messages);
	}



	public function poly_check_request($latitude, $longitude)
	{
		$range_array = [];
		$range_data = GeoFence::select('id','ranges')->where('company_id', $this->company_id)->where('status', 1)->get();
		if(count($range_data)!=0){
			foreach($range_data as $ranges) {
				if(!empty($ranges)){

					$vertices_x = $vertices_y = [];

					$range_values = json_decode($ranges['ranges'],true);

					if(count($range_values)>0){
						foreach($range_values as $range ){
							$vertices_x[] = $range['lng'];
							$vertices_y[] = $range['lat'];
						}
					}

					$points_polygon = count($vertices_x) - 1; 

					if ($this->inPolygon($points_polygon, $vertices_x, $vertices_y, $latitude, $longitude)){
						return $ranges['id'];
					}

				}
			}
		}

		return false;

	}

	public function inPolygon($points_polygon, $vertices_x, $vertices_y, $latitude_y, $longitude_x) {
		$i = $j = $c = 0;
		for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
			if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) && ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) 
			$c = !$c;
		}
		return $c;
	}

   
	public function createRide(Request $request) {

		$geofence =$this->poly_check_request((round($request->s_latitude,6)),(round($request->s_longitude,6)));

		if($geofence == false) {
			return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];
		}

		$ride_city_price = RideCityPrice::where('geofence_id',$geofence)->where('ride_delivery_vehicle_id', $request->service_type)->first();

		if($ride_city_price == null) {
			return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];
		}

 
		$ActiveRequests = RideRequest::PendingRequest($this->user->id)->count();

		if($ActiveRequests > 0) {
			return ['status' => 422, 'message' => trans('api.ride.request_inprogress')];
		}
		
		$timezone =  (Auth::guard('user')->user()->state_id) ? State::find($this->user->state_id)->timezone : '';

		$country =  CompanyCountry::where('country_id', $this->user->country_id)->first();

		$currency =  ($country != null) ? $country->currency : '' ;

		if($request->has('schedule_date') && $request->schedule_date != "" && $request->has('schedule_time') && $request->schedule_time != ""){

			$schedule_date = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC'); 


			$beforeschedule_time = (new Carbon($schedule_date))->subHour(1);
			$afterschedule_time = (new Carbon($schedule_date))->addHour(1);


			$CheckScheduling = RideRequest::where('status','SCHEDULED') 
							->where('user_id', $this->user->id)
							->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
							->count();


			if($CheckScheduling > 0){
				return ['status' => 422, 'message' => trans('api.ride.request_already_scheduled')];
			}

		}

		$distance = $this->settings->transport->provider_search_radius ? $this->settings->transport->provider_search_radius : 100;

		$latitude = $request->s_latitude;
		$longitude = $request->s_longitude;
		$service_type = $request->service_type;


		$child_seat = $request->child_seat != null  ? $request->child_seat : 0 ;
		$wheel_chair = $request->wheel_chair != null ? $request->wheel_chair : 0 ;

		$request->request->add(['latitude' => $request->s_latitude]);
		$request->request->add(['longitude' => $request->s_longitude]);

		$request->request->add(['distance' => $distance]);
		$request->request->add(['provider_negative_balance' => $this->settings->site->provider_negative_balance]);

		$callback = function ($q) use($request) {
			$q->where('admin_service', 'TRANSPORT');
			$q->where('ride_delivery_id',$request->service_type);
		};

		$childseat = function($query) use ($child_seat, $wheel_chair){    
					if($child_seat != 0) {
						$query->where('child_seat', $child_seat);
					}
					if($wheel_chair != 0) {
						$query->where('wheel_chair',$wheel_chair);
					}
					};

		$withCallback = ['service' => $callback, 'service.ride_vehicle'];
		$whereHasCallback = ['service' => $callback, 'service.vehicle' => $childseat];

		$Providers = (new UserServices())->availableProviders($request, $withCallback, $whereHasCallback);

		if(count($Providers) == 0) {
			return ['status' => 422, 'message' => trans('api.ride.no_providers_found')];
		}     

		try {
			$details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".$this->settings->site->server_key;

			$json = Helper::curl($details);

			$details = json_decode($json, TRUE);

			$route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

			$rideRequest = new RideRequest;
			$otp=mt_rand(1000 , 9999);
			$rideRequest->geofence_id = $geofence;
			$rideRequest->company_id = $this->company_id;
			$rideRequest->admin_service = 'TRANSPORT';
			$rideRequest->booking_id = Helper::generate_booking_id('TRNX');
			$rideRequest->user_id = $this->user->id;
			$rideRequest->provider_service_id = $request->service_type;
			$rideRequest->ride_type_id = $request->ride_type_id;
			$rideRequest->distance = (count($details['routes']) > 0) ? ($details['routes'][0]['legs'][0]['distance']['value'] / 1000) : 0;
			$rideRequest->payment_mode = $request->payment_mode;
			$rideRequest->promocode_id = $request->promocode_id ? : 0;
			$rideRequest->status = 'SEARCHING';
			$rideRequest->timezone = $timezone;
			$rideRequest->currency = $currency;
			if($this->settings->transport->manual_request == "1") $rideRequest->request_type = "MANUAL";
			$rideRequest->country_id = $this->user->country_id;
			$rideRequest->city_id = $this->user->city_id;
			$rideRequest->s_address = $request->s_address ? $request->s_address : "";
			$rideRequest->d_address = $request->d_address ? $request->d_address  : "";
			$rideRequest->s_latitude = $request->s_latitude;
			$rideRequest->s_longitude = $request->s_longitude;
			$rideRequest->d_latitude = $request->d_latitude;
			$rideRequest->d_longitude = $request->d_longitude;
			$rideRequest->ride_delivery_id = $service_type;
			// dd($request->someone);
			if($request->has('someone') && $request->someone==1){
				$rideRequest->someone=$request->someone;
				$rideRequest->someone_mobile=$request->someone_mobile;
				$rideRequest->someone_email=$request->someone_email;
				 try{
					  if( !empty($this->settings->site->send_email) && $this->settings->site->send_email == 1) {
						 Mail::send('mails/someone', ['settings' => $this->settings,'user'=>$this->user,'otp'=>$otp], function ($mail) use($request) {
							$mail->from($this->settings->site->mail_from_address, $this->settings->site->mail_from_name);
							$mail->to($request->someone_email, $this->user->first_name.' '.$this->user->last_name)->subject('Notification');
						  });
					   }  

				   }catch (\Throwable $e) { 
					   throw new \Exception($e->getMessage());
					}   
			 }
			$rideRequest->track_distance = 1;
			$rideRequest->track_latitude = $request->s_latitude;
			$rideRequest->track_longitude = $request->s_longitude;
			if($request->d_latitude == null && $request->d_longitude == null) $rideRequest->is_drop_location = 0;
			$rideRequest->destination_log = json_encode([['latitude' => $rideRequest->d_latitude, 'longitude' => $request->d_longitude, 'address' => $request->d_address]]);
			$rideRequest->unit = isset($this->settings->site->distance) ? $this->settings->site->distance : 'Kms';
			if($this->user->wallet_balance > 0) $rideRequest->use_wallet = $request->use_wallet ? : 0;
			$rideRequest->is_track = "YES";
			$rideRequest->otp = $otp;
			$rideRequest->assigned_at = Carbon::now();
			$rideRequest->route_key = $route_key;
			if($Providers->count() <= (isset($this->settings->transport->surge_trigger) ? $this->settings->transport->surge_trigger : 0) && $Providers->count() > 0){
				$rideRequest->surge = 1;
			}

			if($request->has('schedule_date') && $request->schedule_date != "" && $request->has('schedule_time') && $request->schedule_time != ""){
				$rideRequest->status = 'SCHEDULED';
				$rideRequest->schedule_at = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->schedule_date. ' ' .$request->schedule_time)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');
				$rideRequest->is_scheduled = 'YES';
			}

			$rideRequest->save();

			// update payment mode
			User::where('id', $this->user->id)->update(['payment_mode' => $request->payment_mode]);

			if($request->has('card_id')){
				Card::where('user_id',Auth::guard('user')->user()->id)->update(['is_default' => 0]);
				Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
			}

			$rideRequest = RideRequest::with('ride', 'ride_type')->where('id', $rideRequest->id)->first();

			(new UserServices())->createRequest($Providers, $rideRequest, 'TRANSPORT');

			return ['message' => ($rideRequest->status == 'SCHEDULED') ? 'Schedule request created!' : 'New request created!', 'data' => [
						'message' => ($rideRequest->status == 'SCHEDULED') ? 'Schedule request created!' : 'New request created!',
						'request' => $rideRequest->id
					]];

		} catch (Exception $e) {  
			throw new \Exception($e->getMessage());
		}
		

	}

	public function cancelRide($request) {

		try{

			$rideRequest = RideRequest::findOrFail($request->id);

			if($rideRequest->status == 'CANCELLED')
			{
				return ['status' => 404, 'message' => trans('api.ride.already_cancelled')];
			}

			if(in_array($rideRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED'])) {

				if($rideRequest->status != 'SEARCHING'){

					$validator = Validator::make($request->all(), [
						'cancel_reason'=> 'max:255',]);

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
				}

				$rideRequest->status = 'CANCELLED';

				if($request->cancel_reason=='ot')
					$rideRequest->cancel_reason = $request->cancel_reason_opt;
				else
					$rideRequest->cancel_reason = $request->cancel_reason;

				$rideRequest->cancelled_by = $request->cancelled_by;
				$rideRequest->save();

				if($request->cancelled_by == "PROVIDER") {
					if($this->settings->site->broadcast_request == 1){
						return ['status' => 200, 'message' => trans('api.ride.request_rejected') ];
					 }else{
						(new ProviderServices())->assignNextProvider($rideRequest->id, $rideRequest->admin_service );
						return ['status' => 200, 'data' => $rideRequest->with('user')->get() ];
					 }
				} else {
					(new UserServices())->cancelRequest($rideRequest);
				}

				return ['status' => 200, 'message' => trans('api.ride.ride_cancelled')];

			} else {

				return ['status' => 403, 'message' => trans('api.ride.already_onride')];
			}
		}

		catch (ModelNotFoundException $e) {
			return $e->getMessage();
		}
	}

	public function extendTrip(Request $request) {
		try{

			$rideRequest = RideRequest::select('id')->findOrFail($request->id);

			$details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$rideRequest->s_latitude.",".$rideRequest->s_longitude."&destination=".$request->latitude.",".$request->longitude."&mode=driving&key=".$this->settings->site->server_key;

			$json = Helper::curl($details);

			$details = json_decode($json, TRUE);

			$route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

			$destination_log = json_decode($rideRequest->destination_log);
			$destination_log[] = ['latitude' => $request->latitude, 'longitude' => $request->longitude, 'address' => $request->address];

			$rideRequest->d_latitude = $request->latitude;
			$rideRequest->d_longitude = $request->longitude;
			$rideRequest->d_address = $request->address;
			$rideRequest->route_key = $route_key;
			$rideRequest->destination_log = json_encode($destination_log);

			$rideRequest->save();

			$message = trans('api.destination_changed');

			(new SendPushNotification)->sendPushToProvider($rideRequest->provider_id, 'transport', $message);

			(new SendPushNotification)->sendPushToUser($rideRequest->user_id, 'transport', $message); 

			//Send message to socket
			$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $rideRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
			app('redis')->publish('checkTransportRequest', json_encode( $requestData ));

			return $rideRequest;

		} catch (\Throwable $e) {
			return $e->getMessage() ;
		}
	}

	public function updateRide(Request $request) { 
		try{

			$ride_otp = $this->settings->transport->ride_otp;

			$rideRequest = RideRequest::with('user')->findOrFail($request->id);

			//Add the Log File for ride
			$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'TRANSPORT')->first();

			if($request->status == 'DROPPED' && $request->d_latitude != null && $request->d_longitude != null) {

				$rideRequest->d_latitude = $request->d_latitude;
				$rideRequest->d_longitude = $request->d_longitude;
				$rideRequest->d_address = $request->d_address;
				$rideRequest->save();

				$details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$rideRequest->s_latitude.",".$rideRequest->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".$siteConfig->server_key;

				$json = Helper::curl($details);

				$details = json_decode($json, TRUE);

				$route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

				$rideRequest->route_key = $route_key;
				
			}


			if($request->status == 'DROPPED' && $rideRequest->payment_mode != 'CASH') {
				$rideRequest->status = 'COMPLETED';
				$rideRequest->paid = 0;

				(new SendPushNotification)->Complete($rideRequest, 'transport');
			} else if ($request->status == 'COMPLETED' && $rideRequest->payment_mode == 'CASH') {
				
				if($rideRequest->status=='COMPLETED'){
					//for off cross clicking on change payment issue on mobile
					return true;
				}
				
				$rideRequest->status = $request->status;
				$rideRequest->paid = 1;                
				
				(new SendPushNotification)->Complete($rideRequest, 'transport');

				//for completed payments
				$RequestPayment = RideRequestPayment::where('ride_request_id', $request->id)->first();
				$RequestPayment->payment_mode = 'CASH';
				$RequestPayment->cash = $RequestPayment->payable;
				$RequestPayment->payable = 0;                
				$RequestPayment->save();               

			} else {
				$rideRequest->status = $request->status;

				if($request->status == 'ARRIVED'){
					(new SendPushNotification)->Arrived($rideRequest, 'transport');
				}
			}

			if($request->status == 'PICKEDUP'){
				if($this->settings->transport->ride_otp==1){
					if(isset($request->otp) && $rideRequest->request_type != "MANUAL"){
						if($request->otp == $rideRequest->otp){
							$rideRequest->started_at = Carbon::now();
							(new SendPushNotification)->Pickedup($rideRequest, 'transport');
						}else{
							header("Access-Control-Allow-Origin: *");
							header("Access-Control-Allow-Headers: *");
							header('Content-Type: application/json');
							http_response_code(400);
							echo json_encode(Helper::getResponse(['status' => 400, 'message' => trans('api.otp'),  'error' => trans('api.otp')])->original);
							exit;
						}
					}else{
						$rideRequest->started_at = Carbon::now();
						(new SendPushNotification)->Pickedup($rideRequest, 'transport');
					}
				}else{
					$rideRequest->started_at = Carbon::now();
					(new SendPushNotification)->Pickedup($rideRequest, 'transport');
				}
			}

			$rideRequest->save();

			if($request->status == 'DROPPED') {

				$waypoints = [];

				$chat=Chat::where('admin_service', 'TRANSPORT')->where('request_id', $rideRequest->id)->where('company_id', Auth::guard('provider')->user()->company_id)->first();

				if($chat != null) {
					$chat->delete();
				}

				if($request->has('distance') && $rideRequest->distance != null) {
					$rideRequest->distance  = ($request->distance / 1000); 
				}

				if($request->has('location_points') && $rideRequest->location_points != null) {

					foreach($request->location_points as $locations) {
						$waypoints[] = $locations['lat'].",".$locations['lng'];
					}

					$details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$rideRequest->s_latitude.",".$rideRequest->s_longitude."&destination=".$request->latitude.",".$request->longitude."&waypoints=" . implode($waypoints, '|')."&mode=driving&key=".$siteConfig->server_key;

					$json = Helper::curl($details);

					$details = json_decode($json, TRUE);

					$route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

					$rideRequest->route_key = $route_key;
					$rideRequest->location_points = json_encode($request->location_points);
				}
				
				$rideRequest->finished_at = Carbon::now();
				$StartedDate  = date_create($rideRequest->started_at);
				$FinisedDate  = Carbon::now();
				$TimeInterval = date_diff($StartedDate,$FinisedDate);
				$MintuesTime  = $TimeInterval->i;
				$rideRequest->travel_time = $MintuesTime;
				$rideRequest->save();
				$rideRequest->with('user')->findOrFail($request->id);
				$rideRequest->invoice = $this->invoice($request->id, ($request->toll_price != null) ? $request->toll_price : 0);
			   
				if($rideRequest->invoice) {
					(new SendPushNotification)->Dropped($rideRequest, 'transport');
				}
				

			}

			$user_request->provider_id = $rideRequest->provider_id;
			$user_request->status = $rideRequest->status;
			$user_request->request_data = json_encode($rideRequest);

			$user_request->save();

			//Send message to socket
			$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $rideRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
			app('redis')->publish('checkTransportRequest', json_encode( $requestData ));
			
			// Send Push Notification to User
			return ['data' => $rideRequest ];

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function calculateFare($request, $cflag=0){

		try{
			$total=$tax_price='';
			$location=$this->getLocationDistance($request);

			$settings = json_decode(json_encode(Setting::where('company_id', $request['company_id'])->first()->settings_data));

			$ride_city_price = RideCityPrice::where('geofence_id',$request['geofence_id'])->where('ride_delivery_vehicle_id', $request['service_type'])->first();

			$siteConfig = $settings->site;
			$transportConfig = $settings->transport;

			if(!empty($location['errors'])){
				throw new Exception($location['errors']);
			}
			else{

				if($transportConfig->unit_measurement=='Kms')
					$total_kilometer = round($location['meter']/1000,1); //TKM
				else
					$total_kilometer = round($location['meter']/1609.344,1); //TMi

				$requestarr['city_id']=$request['city_id'];
				$requestarr['meter']=$total_kilometer;
				$requestarr['time']=$location['time'];
				$requestarr['seconds']=$location['seconds'];
				$requestarr['kilometer']=0;
				$requestarr['minutes']=0;
				$requestarr['service_type']=$request['service_type'];
				$requestarr['city_id']=$request['city_id']; 
				$requestarr['geofence_id']=$request['geofence_id'];               

				$tax_percentage = $ride_city_price->tax;
				$commission_percentage = $ride_city_price->commission;
				$surge_trigger = isset($transportConfig->surge_trigger) ? $transportConfig->surge_trigger : 0 ;
			   
				$price_response=$this->applyPriceLogic($requestarr);

				if($tax_percentage>0){
					$tax_price = $this->applyPercentage($price_response['price'],$tax_percentage);
					$total = $price_response['price'] + $tax_price;
				}
				else{
					$total = $price_response['price'];
				}


				if($cflag!=0){

					if($commission_percentage>0){
						$commission_price = $this->applyPercentage($price_response['price'],$commission_percentage);
						$commission_price = $price_response['price'] + $commission_price;
					}
				   
					$peak = 0;

					$start_time = Carbon::now()->toDateTimeString();
					
					
					$peak_percentage = 1+(0/100)."X";
					
					/*$start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->where('city_id',$request['city_id'])->where('company_id', '=', $request['company_id'])->first();*/ 

					$start_time_check = PeakHour::whereRaw("IF( start_time > end_time,  CONCAT( subdate(CURDATE(), 1), ' ', start_time ), CONCAT( CURDATE(), ' ', start_time ) ) <= '$start_time'")
					->whereRaw("CONCAT( CURDATE(), ' ', end_time ) >= '$start_time'")
					->where('city_id', $request['city_id'])->where('company_id', $request['company_id'])
					->first();

					if($start_time_check){


						$RideCityPrice = RideCityPrice::where('geofence_id', $request['geofence_id'])->where('ride_delivery_vehicle_id', $request['service_type'])->where('company_id', $request['company_id'] )->first();

						$Peakcharges = RidePeakPrice::where('ride_city_price_id', $RideCityPrice->id)->where('ride_delivery_id', $request['service_type'])->where('peak_hour_id',$start_time_check->id)->first();



						if($Peakcharges){                            
							$peak_price=($Peakcharges->peak_price/100) * $total;
							$total += $peak_price;
							$peak = 1;
							$peak_percentage = 1+($Peakcharges->peak_price/100)."X";
						}

					}
				}    

				$return_data['estimated_fare']=$this->applyNumberFormat(floatval($total)); 
				$return_data['distance']=$total_kilometer;    
				$return_data['time']=$location['time'];
				$return_data['tax_price']=$this->applyNumberFormat(floatval($tax_price));    
				$return_data['base_price']=$this->applyNumberFormat(floatval($price_response['base_price']));    
				$return_data['service_type']=(int)$request['service_type'];   
				$return_data['service']=$price_response['service_type'];   

				if(Auth::guard('user')->user()){
					$return_data['peak']=$peak;    
					$return_data['peak_percentage']=$peak_percentage;   
					$return_data['wallet_balance']=$this->applyNumberFormat(floatval(Auth::guard('user')->user()->wallet_balance));  
				}

				$service_response["data"]=$return_data;                    
			}

		} catch(Exception $e) {
			$service_response["errors"]=$e->getMessage();
		}
	
		return $service_response;    
	} 

	public function applyPriceLogic($requestarr,$iflag=0){

		$fn_response=array();

		$ride_city_price = RideCityPrice::where('geofence_id',$requestarr['geofence_id'])->where('ride_delivery_vehicle_id', $requestarr['service_type'])->first();

		if($ride_city_price == null) {
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Headers: *");
			header('Content-Type: application/json');
			http_response_code(400);
			echo json_encode(Helper::getResponse(['status' => 400, 'message' => trans('user.ride.service_not_available_location'),  'error' => trans('user.ride.service_not_available_location')])->original);
			exit;
		}


		$fn_response['service_type']=$requestarr['service_type'];       
		
		if($iflag==0){
			//for estimated fare
			$total_kilometer = $requestarr['meter']; //TKM || TMi
			$total_minutes = round($requestarr['seconds']/60); //TM        
			$total_hours=($requestarr['seconds']/60)/60; //TH
		}
		else{
			//for invoice fare
			$total_kilometer = $requestarr['kilometer']; //TKM || TMi       
			$total_minutes = $requestarr['minutes']; //TM        
			$total_hours= $requestarr['minutes']/60; //TH
		}
	   
		$per_minute= ($ride_city_price == null) ? 0 : $ride_city_price->minute; //PM
		$per_hour= ($ride_city_price == null) ? 0 : $ride_city_price->hour; //PH
		$per_kilometer= ($ride_city_price == null) ? 0 : $ride_city_price->price; //PKM
		$base_distance= ($ride_city_price == null) ? 0 : $ride_city_price->distance; //BD       
		$base_price= ($ride_city_price == null) ? 0 : $ride_city_price->fixed; //BP
		$price = 0;
		if($ride_city_price != null) {
			if($ride_city_price->calculator == 'MIN') {
				//BP+(TM*PM)
				$price = $base_price+($total_minutes * $per_minute);
			} else if($ride_city_price->calculator == 'HOUR') {
				//BP+(TH*PH)
				$price = $base_price+($total_hours * $per_hour);
			} else if($ride_city_price->calculator == 'DISTANCE') {
				//BP+((TKM-BD)*PKM)  
				if($base_distance>$total_kilometer){
					$price = $base_price;
				}else{
					$price = $base_price+(($total_kilometer - $base_distance)*$per_kilometer);            
				}         
			} else if($ride_city_price->calculator == 'DISTANCEMIN') {
				//BP+((TKM-BD)*PKM)+(TM*PM)
				if($base_distance>$total_kilometer){
					$price = $base_price+($total_minutes * $per_minute);
				}
				else{
					$price = $base_price+((($total_kilometer - $base_distance)*$per_kilometer)+($total_minutes * $per_minute));
				}    
			} else if($ride_city_price->calculator == 'DISTANCEHOUR') {
				//BP+((TKM-BD)*PKM)+(TH*PH)
				if($base_distance>$total_kilometer){
					$price = $base_price+($total_hours * $per_hour);
				}
				else{
					$price = $base_price+((($total_kilometer - $base_distance)*$per_kilometer)+($total_hours * $per_hour));
				}    
			} else {
				//by default set Ditance price BP+((TKM-BD)*PKM) 
				$price = $base_price+(($total_kilometer - $base_distance)*$per_kilometer);
			}
		}
		

		$fn_response['price']=$price;
		$fn_response['base_price']=$base_price;
		if($base_distance>$total_kilometer){
			$fn_response['distance_fare']=0;
		}
		else{
			$fn_response['distance_fare']=($total_kilometer - $base_distance)*$per_kilometer;
		}    
		$fn_response['minute_fare']=$total_minutes * $per_minute;
		$fn_response['hour_fare']=$total_hours * $per_hour;
		$fn_response['calculator']=($ride_city_price == null) ? null : $ride_city_price->calculator;;
		$fn_response['ride_city_price']=$ride_city_price;
		
		return $fn_response;
	}

	public function applyPercentage($total,$percentage){
		return ($percentage/100)*$total;
	}

	public function applyNumberFormat($total){
		return round($total, Helper::setting()->site->round_decimal != "" ? Helper::setting()->site->round_decimal : 2 );
	}
	
	public function getLocationDistance($locationarr){

		$fn_response=array('data'=>null,'errors'=>null);

		try{

			$s_latitude = $locationarr['s_latitude'];
			$s_longitude = $locationarr['s_longitude'];
			$d_latitude = empty($locationarr['d_latitude']) ? $locationarr['s_latitude'] : $locationarr['d_latitude'];
			$d_longitude = empty($locationarr['d_longitude']) ? $locationarr['s_longitude'] : $locationarr['d_longitude'];

			$apiurl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$s_latitude.",".$s_longitude."&destinations=".$d_latitude.",".$d_longitude."&mode=driving&sensor=false&units=imperial&key=".$locationarr['server_key'];

			$client = new Client;
			$location = $client->get($apiurl);           
			$location = json_decode($location->getBody(),true);
		   
			if(!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status']=='ZERO_RESULTS'){
				throw new Exception("Out of service area", 1);
				
			}
			$fn_response["meter"]=$location['rows'][0]['elements'][0]['distance']['value'];
			$fn_response["time"]=$location['rows'][0]['elements'][0]['duration']['text'];
			$fn_response["seconds"]=$location['rows'][0]['elements'][0]['duration']['value'];

		}
		catch(Exception $e){
			$fn_response["errors"]=trans('user.maperror');
		}      

		return $fn_response;    
	}

	public function invoice($request_id, $toll_price = 0)
	{
		try {                      

			$rideRequest = RideRequest::with('provider')->findOrFail($request_id);   

			/*$RideCommission = RideCity::where('city_id',$rideRequest->city_id)->first();
			$tax_percentage = $RideCommission->tax ? $RideCommission->tax : 0;
			$commission_percentage = $RideCommission->comission ? $RideCommission->comission : 0;
			$waiting_percentage = $RideCommission->waiting_percentage ? $RideCommission->waiting_percentage : 0;
			$peak_percentage = $RideCommission->peak_percentage ? $RideCommission->peak_percentage : 0;*/

			$tax_percentage = $commission_percentage = $waiting_percentage = $peak_percentage =0;

			$Fixed = 0;
			$Distance = 0;
			$Discount = 0; // Promo Code discounts should be added here.
			$Wallet = 0;            
			$ProviderPay = 0;
			$Distance_fare =0;
			$Minute_fare =0;
			$calculator ='DISTANCE';
			$discount_per =0;

			//added the common function for calculate the price
			$requestarr['kilometer']=$rideRequest->distance;
			$requestarr['time']=0;
			$requestarr['seconds']=0;
			$requestarr['minutes']=$rideRequest->travel_time;
			$requestarr['ride_delivery_id']=$rideRequest->ride_delivery_id;
			$requestarr['city_id']=$rideRequest->city_id;
			$requestarr['service_type']=$rideRequest->ride_delivery_id;
			$requestarr['geofence_id']=$rideRequest->geofence_id;
			
			$response = new Ride();         
			$pricedata=$response->applyPriceLogic($requestarr,1);

			/*$newRequest = RideRequest::findOrFail($rideRequest->id);
			$newRequest->status = "PICKEDUP";
			$newRequest->save();
			dd($pricedata);
			return false;*/


			
			if(!empty($pricedata)){
				$Distance =$pricedata['price'];
				$Fixed = $pricedata['base_price'];
				$Distance_fare = $pricedata['distance_fare'];
				$Minute_fare = $pricedata['minute_fare'];
				$Hour_fare = $pricedata['hour_fare'];
				$calculator = $pricedata['calculator'];
				$RideCityPrice = $pricedata['ride_city_price'];
				$rideRequest->calculator=$pricedata['calculator'];
				$rideRequest->save();

				$tax_percentage = isset($RideCityPrice->tax) ? $RideCityPrice->tax : 0;
				$commission_percentage = isset($RideCityPrice->commission) ? $RideCityPrice->commission : 0;
				$waiting_percentage = isset($RideCityPrice->waiting_commission) ? $RideCityPrice->waiting_commission : 0;
				$peak_percentage = isset($RideCityPrice->peak_commission) ? $RideCityPrice->peak_commission : 0;
			}
			 
			
			$Distance=$Distance;
			$Tax = ($Distance) * ( $tax_percentage/100 );
			

			if($rideRequest->promocode_id>0){
				if($Promocode = Promocode::find($rideRequest->promocode_id)){
					$max_amount = $Promocode->max_amount;
					$discount_per = $Promocode->percentage;

					$discount_amount = (($Distance + $Tax) * ($discount_per/100));

					if($discount_amount>$Promocode->max_amount){
						$Discount = $Promocode->max_amount;
					}
					else{
						$Discount = $discount_amount;
					}

					$PromocodeUsage = new PromocodeUsage;
					$PromocodeUsage->user_id =$rideRequest->user_id;
					$PromocodeUsage->company_id =Auth::guard('provider')->user()->company_id;
					$PromocodeUsage->promocode_id =$rideRequest->promocode_id;
					$PromocodeUsage->status ='USED';
					$PromocodeUsage->save();

					// $Total = $Distance + $Tax;
					// $payable_amount = $Distance + $Tax - $Discount;

				}                
			}
		   
			$Total = $Distance + $Tax;
			$payable_amount = $Distance + $Tax - $Discount;


			if($Total < 0){
				$Total = 0.00; // prevent from negative value
				$payable_amount = 0.00;
			}


			//changed by tamil
			$Commision = ($Total) * ( $commission_percentage/100 );
			$Total += $Commision;
			$payable_amount += $Commision;
			
			$ProviderPay = (($Total+$Discount) - $Commision)-$Tax;

			$Payment = new RideRequestPayment;


			$Payment->company_id = Auth::guard('provider')->user()->company_id;
			$Payment->ride_request_id = $rideRequest->id;

			$Payment->user_id=$rideRequest->user_id;
			$Payment->provider_id=$rideRequest->provider_id;

			if(!empty($rideRequest->admin_id)){
				$Fleet = Admin::where('id',$rideRequest->admin_id)->where('type','FLEET')->where('company_id',Auth::guard('provider')->user()->company_id)->first();

				$fleet_per=0;

				if(!empty($Fleet)){
					if(!empty($Commision)){                                     
						$fleet_per=$Fleet->commision ? $Fleet->commision : 0;
					}
					else{
						$fleet_per=$RideCityPrice->fleet_commission ? $RideCityPrice->fleet_commission :0;
					}

					$Payment->fleet_id=$rideRequest->provider->admin_id;
					$Payment->fleet_percent=$fleet_per;
				}
			}


			//check peakhours and waiting charges
			$total_waiting_time=$total_waiting_amount=$peakamount=$peak_comm_amount=$waiting_comm_amount=0;

			if($RideCityPrice->waiting_min_charge>0){
				$total_waiting=round($this->total_waiting($rideRequest->id)/60);
				if($total_waiting>0){
					if($total_waiting > $RideCityPrice->waiting_free_mins){
						$total_waiting_time = $total_waiting - $RideCityPrice->waiting_free_mins;
						$total_waiting_amount = $total_waiting_time * $RideCityPrice->waiting_min_charge;
						$waiting_comm_amount = ($waiting_percentage/100) * $total_waiting_amount;

					}
				}
			}

			$start_time = $rideRequest->started_at;
			$end_time = $rideRequest->finished_at;

			$start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->where('timezone', $rideRequest->timezone)->where('company_id', Auth::guard('provider')->user()->company_id)->first();

			if($start_time_check){

				$RideCityPriceList = RideCityPrice::where('geofence_id',$rideRequest->geofence_id)->where('ride_delivery_vehicle_id',$rideRequest->ride_delivery_id)->where('company_id', Auth::guard('provider')->user()->company_id)->first();

				$Peakcharges = RidePeakPrice::where('ride_city_price_id',$RideCityPriceList->id)->where('ride_delivery_id',$rideRequest->ride_delivery_id)->where('peak_hour_id',$start_time_check->id)->first();


				if($Peakcharges){
					$peakamount=($Peakcharges->peak_price/100) * $Fixed;
					$peak_comm_amount = ($peak_percentage/100) * $peakamount;
				}

			}

			$Total += $peakamount+$total_waiting_amount+$toll_price;
			$payable_amount += $peakamount+$total_waiting_amount+$toll_price;

			$ProviderPay = $ProviderPay + ($peakamount+$total_waiting_amount) + $toll_price;

			$Payment->fixed = $Fixed + $Commision + $peakamount;
			$Payment->distance = $Distance_fare;
			$Payment->minute  = $Minute_fare;
			$Payment->hour  = $Hour_fare;
			$Payment->payment_mode  = $rideRequest->payment_mode;
			$Payment->commision = $Commision;
			$Payment->commision_percent = $commission_percentage;
			$Payment->toll_charge = $toll_price;
			$Payment->total = $Total;
			$Payment->provider_pay = $ProviderPay;
			$Payment->peak_amount = $peakamount;
			$Payment->peak_comm_amount = $peak_comm_amount;
			$Payment->total_waiting_time = $total_waiting_time;
			$Payment->waiting_amount = $total_waiting_amount;
			$Payment->waiting_comm_amount = $waiting_comm_amount;
			if($rideRequest->promocode_id>0){
				$Payment->promocode_id = $rideRequest->promocode_id;
			}
			$Payment->discount = $Discount;
			$Payment->discount_percent = $discount_per;
			$Payment->company_id = Auth::guard('provider')->user()->company_id;


			if($Discount  == ($Distance + $Tax)){
				$rideRequest->paid = 1;
			}

			if($rideRequest->use_wallet == 1 && $payable_amount > 0){

				$User = User::find($rideRequest->user_id);
				$currencySymbol = $rideRequest->currency;
				$Wallet = $User->wallet_balance;

				if($Wallet != 0){

					if($payable_amount > $Wallet) {

						$Payment->wallet = $Wallet;
						$Payment->is_partial=1;
						$Payable = $payable_amount - $Wallet;
						
						$Payment->payable = abs($Payable);

						$wallet_det=$Wallet;  

						if($rideRequest->payment_mode == 'CASH'){
							$Payment->round_of = round($Payable)-abs($Payable);
							$Payment->total = $Total;
							$Payment->payable = round($Payable);
						}                    

					} else {

						$Payment->payable = 0;
						$WalletBalance = $Wallet - $payable_amount;
						
						$Payment->wallet = $payable_amount;
						
						$Payment->payment_id = 'WALLET';
						$Payment->payment_mode = $rideRequest->payment_mode;

						$rideRequest->paid = 1;
						$rideRequest->status = 'COMPLETED';
						$rideRequest->save();

						$wallet_det=$payable_amount;
					   
					}
					
					(new SendPushNotification)->ChargedWalletMoney($rideRequest->user_id,Helper::currencyFormat($wallet_det,$currencySymbol), 'transport');

					//for create the user wallet transaction

					$transaction['amount']=$wallet_det;
					$transaction['id']=$rideRequest->user_id;
					$transaction['transaction_id']=$rideRequest->id;
					$transaction['transaction_alias']=$rideRequest->booking_id;
					$transaction['company_id']=$rideRequest->company_id;
					$transaction['transaction_msg']='transport deduction';

					(new Transactions)->userCreditDebit($transaction,0);

				}

			} else {
				if($rideRequest->payment_mode == 'CASH'){
					$Payment->round_of = round($payable_amount)-abs($payable_amount);
					$Payment->total = $Total;
					$Payment->payable = round($payable_amount);
				}
				else{
					$Payment->total = abs($Total);
					$Payment->payable = abs($payable_amount);   
				}               
			}

			$Payment->tax = $Tax;

			$Payment->tax_percent = $tax_percentage;

			$Payment->save();

			return $Payment;

		} catch (\Throwable $e) {
			$newRequest = RideRequest::findOrFail($rideRequest->id);
			$newRequest->status = "PICKEDUP";
			$newRequest->save();
			return false;
		}
	}

	public function total_waiting($id){

		$waiting = RideRequestWaitingTime::where('ride_request_id', $id)->whereNotNull('ended_at')->sum('waiting_mins');

		$uncounted_waiting = RideRequestWaitingTime::where('ride_request_id', $id)->whereNull('ended_at')->first();

		if($uncounted_waiting != null) {
			$waiting += (Carbon::parse($uncounted_waiting->started_at))->diffInSeconds(Carbon::now());
		}

		return $waiting;
	}
}