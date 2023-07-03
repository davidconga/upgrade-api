<?php

namespace App\Http\Controllers\V1\Common\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Setting;
use App\Models\Common\User;
use App\Models\Common\Provider;
use App\Models\Common\Admin;
use App\Models\Common\AdminWallet;
use App\Models\Common\CompanyCountry;
use App\Helpers\Helper;
use DB;

class AdminController extends Controller
{
    public function settings_store(Request $request)
    {    
        $setting = Setting::where('company_id', \Auth::user()->company_id)->first();

        if($setting->demo_mode == 1) {
            return Helper::getResponse(['status' => 403, 'message' => trans('admin.demomode') ]);
        }

        if($setting != null) {
            $data = json_decode(json_encode($setting->settings_data), true);

            if($request->has('mail_driver')) {
                if(!$request->has('send_email')) {
                    $request->request->add(['send_email' => '0']);
                }
            }

            if($request->has('referral_count')) {
                if(!$request->has('referral')) {
                    $request->request->add(['referral' => '0']);
                }
            }

            if($request->has('facebook_app_id')) {

                if(!$request->has('social_login')) {
                    $request->request->add(['social_login' => '0']);
                }

            }

            if($request->has('provider_select_timeout')) {

                if(!$request->has('ride_otp')) {
                    $request->request->add(['ride_otp' => '0']);
                }

                if(!$request->has('manual_request')) {
                    $request->request->add(['manual_request' => '0']);
                }

                if(!$request->has('broadcast_request')) {
                    $request->request->add(['broadcast_request' => '0']);
                }

            }

            if($request->has('service_provider_select_timeout')) {

                if(!$request->has('service_serve_otp')) {
                    $request->request->add(['service_serve_otp' => '0']);
                }

                if(!$request->has('service_manual_request')) {
                    $request->request->add(['service_manual_request' => '0']);
                }

                if(!$request->has('service_broadcast_request')) {
                    $request->request->add(['service_broadcast_request' => '0']);
                }

            }

            if($request->has('store_response_time')) {
                if(!$request->has('order_otp')) {
                    $request->request->add(['order_otp' => '0']);
                }

                if(!$request->has('store_manual_request')) {
                    $request->request->add(['store_manual_request' => '0']);
                }

                /*if(!$request->has('store_broadcast_request')) {
                    $request->request->add(['store_broadcast_request' => '0']);
                }*/
            }


            foreach($request->except(['payment_name', 'payment_status', 'payment_key_name', 'payment_key_value','ride_otp', 'manual_request', 'broadcast_request', 'tax_percentage', 'commission_percentage', 'surge_trigger', 'provider_search_radius','store_search_radius', 'user_select_timeout', 'provider_select_timeout', 'surge_percentage', 'track_distance', 'booking_prefix', 'contact_number', 'service_provider_select_timeout', 'service_provider_search_radius', 'service_time_left_to_respond', 'service_tax_percentage', 'service_commission_percentage', 'service_surge_trigger', 'service_surge_percentage','service_manual_request', 'service_broadcast_request', 'service_serve_otp', 'service_booking_prefix', 'service_track_distance','store_search_radius', 'store_response_time', 'store_manual_request', 'store_broadcast_request', 'order_otp','max_items_in_order']) as $key => $req) {
                if(!empty($data['site'][$key])) {
                    $data['site'][$key] = $request->$key;
                } else {
                    $data['site'][$key] = $request->$key;
                }
            }

            foreach($request->only(['contact_number']) as $number) {

                $contact = new \stdClass();
                $contact->number = $number;

                $data['site']['contact_number'] = [$contact];

            }


            

            foreach($request->only(['user_select_timeout', 'provider_select_timeout', 'provider_search_radius', 'unit_measurement', 'manual_request', 'broadcast_request', 'ride_otp', 'booking_prefix']) as $key => $req) {

                if(!empty($data['transport'][$key])) {
                    $data['transport'][$key] = $request->$key;
                } else {
                    $data['transport'][$key] = $request->$key;
                }
            }
            $data['transport']['destination'] = $data['transport']['destination'];
            foreach($request->only(['service_provider_select_timeout', 'service_provider_search_radius', 'service_time_left_to_respond', 'service_tax_percentage', 'service_commission_percentage', 'service_surge_trigger', 'service_surge_percentage','service_manual_request', 'service_broadcast_request', 'service_serve_otp', 'service_booking_prefix', 'service_track_distance']) as $key => $req) {

                $key_name= str_replace(substr($key,0,strpos($key,'_')).'_', '', $key);

                if(!empty($data['service'][$key])) {
                    $data['service'][$key_name] = $request->$key;
                } else {
                    $data['service'][$key_name] = $request->$key;
                }
            }

            foreach($request->only(['store_search_radius', 'store_response_time', 'store_provider_select_timeout', 'store_manual_request', 'order_otp','booking_prefix','max_items_in_order']) as $key => $req) {
                if($key == 'store_manual_request' || $key == 'store_provider_select_timeout'  ){
                    $key_name = str_replace(substr($key,0,strpos($key,'_')).'_', '', $key);
                }else{
                    $key_name = $key;
                }

                if(!empty($data['order'][$key])) {
                    $data['order'][$key_name] = $request->$key;
                } else {
                    $data['order'][$key_name] = $request->$key;
                }
            }
            //dd($data);
            $payment_name = $request->payment_name;
            $payment_status = $request->payment_status;
            $payment_key_name = $request->payment_key_name;
            $payment_key_value = $request->payment_key_value;

            if($request->has('payment_name')) {
                unset($data["payment"]);
                foreach($request->payment_name as $key => $value) {

                    $credentials = [];

                    foreach ($payment_key_name as $k => $credential_name) {
                        if (preg_match("#^".$key."_#", $credential_name) === 1) {
                            $credentials[] = ["name" => str_replace($key."_", "", $credential_name), "value" => str_replace($key."_", "", $payment_key_value[$k]) ];
                        }
                    }
                    
                    $data["payment"][] = ["name" => $value, "status" => isset($payment_status[$key]) ?  $payment_status[$key] : "0", "credentials" => $credentials ];
                    
                }
            }

            if($request->hasFile('site_icon')) {
                $site_icon = Helper::upload_file($request->file('site_icon'), 'site', 'site_icon.'.$request->file('site_icon')->extension());
                $data['site']['site_icon'] = $site_icon;
            }

            if($request->hasFile('site_logo')) {
                $site_logo = Helper::upload_file($request->file('site_logo'), 'site', 'site_logo.'.$request->file('site_icon')->extension());
                $data['site']['site_logo'] = $site_logo;
            }

            if($request->hasFile('user_pem')) {
                $user_pem = Helper::upload_file($request->file('user_pem'), 'apns', 'user.pem');
                $data['site']['user_pem'] = $user_pem;
            }

            if($request->hasFile('provider_pem')) {
                $provider_pem = Helper::upload_file($request->file('provider_pem'), 'apns', 'provider.pem');
                $data['site']['provider_pem'] = $provider_pem;
            }

            $setting->settings_data = json_encode($data);
            $setting->save();

            //Send message to socket
            $requestData = ['type' => 'SETTING'];
            app('redis')->publish('settingsUpdate', json_encode( $requestData ));

            return Helper::getResponse(['status' => 200,'data'=>json_encode($data)]);

        } else {

            return Helper::getResponse(['status' => 404]);

        }
   
    }
 
    public function dashboarddata($id)
    {
      try{

          $data['currency']=CompanyCountry::where('country_id',$id)->select('currency')->first();
          $data['userdata']=User::where('country_id', $id)->where('company_id',\Auth::user()->company_id)->count();
          $data['providerdata']=Provider::where('country_id', $id)->where('company_id',\Auth::user()->company_id)->count();
          $data['fleetdata']=Admin::where('country_id', $id)->where('type','FLEET')->where('company_id',\Auth::user()->company_id)->count();

            $data['admin'] = AdminWallet::where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['provider_debit'] = Provider::select(DB::raw('SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit'))->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->get()->toArray();
            $data['provider_credit'] = Provider::select(DB::raw('SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit'))->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->get()->toArray();
            $data['fleet_debit'] = Admin::select(DB::raw('SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit'))->where('type','FLEET')->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->get()->toArray();
            $data['fleet_credit'] = Admin::select(DB::raw('SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit'))->where('type','FLEET')->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->get()->toArray();

            $data['admin_tax'] = AdminWallet::where('transaction_type',9)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['admin_commission'] = AdminWallet::where('transaction_type',1)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['admin_discount'] = AdminWallet::where('transaction_type',10)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['admin_referral'] = AdminWallet::where('transaction_type',12)->orWhere('transaction_type',13)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['admin_dispute'] = AdminWallet::where('transaction_type',16)->orWhere('transaction_type',17)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['peak_commission'] = AdminWallet::where('transaction_type',14)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            $data['waiting_commission'] = AdminWallet::where('transaction_type',15)->where('country_id', $id)->where('company_id',\Auth::user()->company_id)->sum('amount');
            return Helper::getResponse(['status' => 200,'data'=>$data]);

         }
         catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
      
   }   

 
  



}
