<?php

namespace App\Http\Controllers\V1\Service\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Service\ServiceCategory;
use App\Models\Service\ServiceSubcategory;
use App\Models\Service\Service;
use App\Models\Service\ServiceRequest;
use App\Services\SendPushNotification;
use App\Models\Common\UserRequest;
use App\Models\Common\RequestFilter;
use App\Models\Common\AdminService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use Carbon\Carbon;
use Auth;
use DB;

class HomeController extends Controller
{

		public function categories(Request $request)
		{
			try{
					$servicecategory = ServiceCategory::whereHas('services.servicescityprice',function($query) {
                           $query->where('city_id',Auth::guard('provider')->user()->city_id);
                       })->where('company_id',Auth::guard('provider')->user()->company_id)->where('service_category_status',1)->with(['providerservicecategory'])->get();
					return Helper::getResponse(['data' => $servicecategory ]);
				}catch (ModelNotFoundException $e) {
					return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
				}

		}

		public function subcategories(Request $request)
		{
             
          $this->validate($request,[
			'service_category_id' => 'required',
		]);


				try{
						$servicesubcategory=ServiceSubcategory::with('providerservicesubcategory')->where('company_id',Auth::guard('provider')->user()->company_id)->where('service_category_id',$request->service_category_id)->where('service_subcategory_status',1)->get();
						return Helper::getResponse(['data' => $servicesubcategory ]);
				}catch (ModelNotFoundException $e) {
						return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
				}

		}

		public function service(Request $request)
			{
				 $this->validate($request,[
				'service_category_id' => 'required',
				'service_subcategory_id' => 'required',
			]);
			try{
				
				$servicesubcategory=Service::with(['providerservices','service_city'=>function($q) {
				     $q->where('city_id', Auth::guard('provider')->user()->city_id);
				}])->where('service_subcategory_id',$request->service_subcategory_id)
				->where('service_category_id', $request->service_category_id)->where('service_status', 1)->get();
				return Helper::getResponse(['data' => $servicesubcategory ]);
			}
			catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
			}

		}

		public function totalservices(Request $request)
		{
			
			try{
               $category=Service::with(['serviceCategory','servicesubCategory','service_city'=>function($q) {
                    $q->where('city_id',Auth::guard('provider')->user()->city_id);
               },'providerservices'])->where('company_id',Auth::guard('provider')->user()->company_id)->where('service_status',1)->get();

               // return $category[0]->service_city;
               $data=[];
               foreach($category as $k=>$v){
               	if($v->serviceCategory) {
               		$category_name=$v->serviceCategory->service_category_name;
               		$category_id=$v->serviceCategory->id;
               		$subcategory=$v->servicesubCategory->service_subcategory_name;
               		$subcategory_id=$v->servicesubCategory->id;
               		$price_choose=$v->serviceCategory->price_choose;
               		$provider_service=count($v->providerservices) > 0 ?  $v->providerservices[0]->id:null;
               		if(count($v->providerservices) > 0){
               			$base_price=$v->providerservices[count($v->providerservices)-1]->base_fare;
               			$per_mile=$v->providerservices[count($v->providerservices)-1]->per_miles;
               			$per_mins=$v->providerservices[count($v->providerservices)-1]->per_mins;
               		}else{
               			$base_price= !empty($v->service_city['base_fare'])? $v->service_city['base_fare'] :0.00;
               			$per_mile= !empty($v->service_city['per_miles'])? $v->service_city['per_miles'] :0.00;
               			$per_mins= !empty($v->service_city['per_mins'])? $v->service_city['per_mins'] :0.00;
               		}
               		$data[$category_name.'-'.$subcategory]['name'][]=$v->service_name;
               		$data[$category_name.'-'.$subcategory]['id'][]=$v->id;
               		$data[$category_name.'-'.$subcategory]['category_id'][]=$category_id;
               		$data[$category_name.'-'.$subcategory]['sub_category_id'][]=$subcategory_id;
               		$data[$category_name.'-'.$subcategory]['price'][]=$base_price;
               		$data[$category_name.'-'.$subcategory]['per_mile'][]=$per_mile;
               		$data[$category_name.'-'.$subcategory]['per_mins'][]=$per_mins;
               		$data[$category_name.'-'.$subcategory]['price_choose'][]=$price_choose;
	                	// echo $v->service_city['fare_type']." - (". $category_name.'-'.$subcategory." - ".$v->service_name." )" ; 
               		$data[$category_name.'-'.$subcategory]['fare_type'][]= !empty($v->service_city['fare_type'])? $v->service_city['fare_type'] :'Not Price';
               		$data[$category_name.'-'.$subcategory]['provider_service_id'][]=$provider_service;
               		$data[$category_name.'-'.$subcategory]['currency_symbol'][]=isset(Auth::guard('provider')->user()->currency_symbol)? Auth::guard('provider')->user()->currency_symbol : "$";               		
               	}
               }
             return Helper::getResponse(['data' =>   $data ]);

			}
			catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
			}

		}

		public function assign_next_provider($request_id) 
		{
				try {
					$userRequest = UserRequest::where('request_id', $request_id)->first();
				} catch (ModelNotFoundException $e) {
					// Cancelled between update.
					return false;
				}

				$admin_service = AdminService::find($userRequest->admin_service)->where('company_id', Auth::guard('provider')->user()->company_id)->first();

				try {
						if($admin_service != null && $admin_service->admin_service == "SERVICE" ) {
							$newRequest = \App\Models\Service\ServiceRequest::with('user')->find($userRequest->request_id);
						}
				} catch(\Throwable $e) { }

				$RequestFilter = RequestFilter::where('request_id', $userRequest->id)->orderBy('id')->first();

				if($RequestFilter != null) {
					$RequestFilter->delete();
				}				

			try {
				$next_provider = RequestFilter::where('request_id', $userRequest->id)->orderBy('id')->first();
				if($next_provider != null) {
					$newRequest->assigned_at = Carbon::now();
					$newRequest->save();
					// incoming request push to provider
					(new SendPushNotification)->serviceIncomingRequest($next_provider->provider_id, 'service_incoming_request');
				} else {
					$userRequest->delete();
					$newRequest->status = 'CANCELLED';
					$newRequest->save();
				}
				
			} catch (ModelNotFoundException $e) {
				RideRequest::where('id', $newRequest->id)->update(['status' => 'CANCELLED']);
				// No longer need request specific rows from RequestMeta
				$RequestFilter = RequestFilter::where('request_id', $userRequest->id)->orderBy('id')->first();
				if($RequestFilter != null) {
					$RequestFilter->delete();
				}
				//  request push to user provider not available
				(new SendPushNotification)->serviceProviderNotAvailable($userRequest->user_id, 'service');
			}
		}
    
}
