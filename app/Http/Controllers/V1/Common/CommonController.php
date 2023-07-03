<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\Setting;
use Illuminate\Http\Request;
use App\Models\Common\AdminService;
use App\Models\Common\CompanyCountry;
use App\Services\SendPushNotification;
use App\Models\Common\CompanyCity;
use App\Models\Common\UserRequest;
use App\Models\Common\Company;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\City;
use App\Models\Common\Menu;
use App\Models\Common\CmsPage;
use App\Models\Common\Rating;
use App\Models\Common\AuthLog;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderWallet;
use App\Models\Common\FleetWallet;
use App\Models\Common\AuthMobileOtp;
use App\Models\Common\Chat;
use App\Helpers\Helper;
use Carbon\Carbon;
use Auth;

class CommonController extends Controller
{
    
    public function base(Request $request) {
       
        $this->validate($request, [
            'salt_key' => 'required',
        ]);

        $license = Company::find(base64_decode($request->salt_key));
        
        if ($license != null) {
            try{  
            if (Carbon::parse($license->expiry_date)->lt(Carbon::now())) {
                return response()->json(['message' => 'License Expired'], 503);
            }

            $admin_service = AdminService::where('company_id', $license->id)->where('status', 1)->get();
           
            //$settings = Setting::where('company_id', $license->id)->first();

            $base_url = $license->base_url;

        $setting = Setting::where('company_id', $license->id)->first();
        $settings = json_decode(json_encode($setting->settings_data));
       
        $appsettings=[];
        if(count($settings)>0){
          
         $appsettings['demo_mode'] = (int)$setting->demo_mode;
         $appsettings['provider_negative_balance'] = (isset($settings->site->provider_negative_balance)) ? $settings->site->provider_negative_balance : '';
         $appsettings['android_key'] = (isset($settings->site->android_key)) ? $settings->site->android_key : '';
         $appsettings['ios_key'] = (isset($settings->site->ios_key)) ? $settings->site->ios_key : '';
         $appsettings['referral'] = ($settings->site->referral ==1) ? 1 : 0;
         $appsettings['send_sms'] = ($settings->site->send_sms == 1) ? 1 : 0;
         $appsettings['social_login'] = ($settings->site->social_login ==1) ? 1 :0;
         $appsettings['otp_verify'] = ($settings->transport->ride_otp == 1) ? 1 : 0;
         
         $appsettings['ride_otp'] = ($settings->transport->ride_otp == 1) ? 1 : 0;
         
         $appsettings['order_otp'] = ($settings->order->order_otp == 1) ? 1 : 0;
       
        
         $appsettings['service_otp'] = ($settings->service->serve_otp == 1) ? 1 : 0;
         $appsettings['payments'] = (count($settings->payment) > 0) ? $settings->payment : 0;
         
         $appsettings['cmspage']['privacypolicy'] = (isset($settings->site->page_privacy)) ? $settings->site->page_privacy : 0;
         $appsettings['cmspage']['help'] = (isset($settings->site->help)) ? $settings->site->help : 0;
         $appsettings['cmspage']['terms'] = (isset($settings->site->terms)) ? $settings->site->terms : 0;
         $appsettings['cmspage']['cancel'] = (isset($settings->site->cancel)) ? $settings->site->cancel : 0;
         $appsettings['supportdetails']['contact_number'] = (isset($settings->site->contact_number) > 0) ? $settings->site->contact_number : 0;
         $appsettings['supportdetails']['contact_email']=(isset($settings->site->contact_email) > 0) ? $settings->site->contact_email : 0;
         $appsettings['languages']=(isset($settings->site->language) > 0) ? $settings->site->language : 0;
        
         }
              return Helper::getResponse(['status' => 200, 'data' => ['base_url' => $base_url, 'services' => $admin_service,'appsetting'=>$appsettings ]]);
            }catch (Exception $e) {
               
                return Helper::getResponse(['status' => 500, 'message' => trans('Something Went Wrong'), 'error' => $e->getMessage() ]);
            }
        }
    }

     public function sendOtp(Request $request) {

        $this->validate($request, [
            'country_code' => 'required',
            'mobile' => 'required',
            'salt_key' => 'required',
        ]);


        $company_id=base64_decode($request->salt_key);

        $otp = $this->createOtp($company_id);

        AuthMobileOtp::updateOrCreate(['company_id' => $company_id, 'country_code' => $request->country_code, 'mobile' => $request->mobile],['otp' => $otp]);

        $send_sms = Helper::send_sms($company_id, '+'.$request->country_code.''.$request->mobile, 'HI '.$otp. ' is your verification code' );

        if($send_sms == 1) {
            return Helper::getResponse(['message' => 'OTP sent! '.$otp]);
        } else {
            return Helper::getResponse(['status' => '400', 'message' => 'Could not send SMS notification. Please try again!', 'error' => $send_sms]);
        }

        
    }

    public function createOtp($company_id) {

        $otp = mt_rand(1111, 9999);

        $auth_mobile_otp = AuthMobileOtp::select('id')->where('otp', $otp)->where('company_id', $company_id)->orderBy('id', 'desc')->first();

        if($auth_mobile_otp != null) {
            $this->createOtp($company_id);
        } else {
            return $otp ;
        } 
    }


      public function verifyOtp(Request $request) {

        $this->validate($request, [
            'country_code' => 'required',
            'mobile' => 'required',
            'otp' => 'required',
            'salt_key' => 'required',
        ]);


        $company_id=base64_decode($request->salt_key);

        $auth_mobile_otp = AuthMobileOtp::where('country_code', $request->country_code)->where('mobile', $request->mobile)->where('otp', $request->otp)->where('updated_at','>=',Carbon::now()->subMinutes(10))->where('company_id', $company_id)->first();

        if($auth_mobile_otp != null) {

            $auth_mobile_otp->delete();

            return Helper::getResponse([ 'message' => 'OTP sent!' ]);
        } else {

            return Helper::getResponse([ 'status' => '400', 'message' => 'OTP error!' ]);

        }


            

        
    }

    public function admin_services() {

        $admin_service = AdminService::where('company_id', Auth::user()->company_id)->whereNotIn('admin_service', ['ORDER'] )->where('status', 1)->get();

        return Helper::getResponse(['status' => 200, 'data' => $admin_service]);

    }

    public function countries_list() {
        $countries = Country::get();
        return Helper::getResponse(['data' => $countries]);
    }

    public function states_list($id) {
        $states = State::where('country_id', $id)->get();
        return Helper::getResponse(['data' => $states]);
    }

    public function cities_list($id) {
        $cities = City::where('state_id', $id)->get();
        return Helper::getResponse(['data' => $cities]);
    }

    public function cmspagetype($type) {
        $cities = CmsPage::where('page_name', $type)->where()->get();
        return Helper::getResponse(['data' => $cities]);
    }

    public function rating($request) {

        Rating::create([
                    'company_id' => $request->company_id,
                    'admin_service' => $request->admin_service,
                    'provider_id' => $request->provider_id,
                    'user_id' => $request->user_id,
                    'request_id' => $request->id,
                    'provider_rating' => $request->rating,
                    'provider_comment' => $request->comment,
                  ]);

        return true;
    }

    public function logdata($type, $id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(7);

        $datum = AuthLog::where('user_type', $type)->where('user_id', $id)->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(5);

        return Helper::getResponse(['data' => $datum]);
    }

    public function walletDetails($type, $id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(15);

        if($type == "User"){
            $datum = UserWallet::with('user')->where('user_id', $id)->select('*',\DB::raw('DATEDIFF(now(),created_at) as days'),\DB::raw('TIMEDIFF(now(),created_at) as total_time'));

        }elseif ($type == "Provider") {
            $datum = ProviderWallet::with('provider')->where('provider_id', $id);
        }elseif ($type == "Fleet") {
            $datum = FleetWallet::where('fleet_id', $id);
        }

        $wallet_details = $datum->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(10);

        return Helper::getResponse(['data' => $wallet_details]);
    }

    public function chat(Request $request) 
    {

        $this->validate($request,[
            'id' => 'required',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
            'salt_key' => 'required',
            'user_name' => 'required',
            'provider_name' => 'required',
            'type' => 'required',
            'message' => 'required'
        ]);

        $company_id = base64_decode($request->salt_key);

        $user_request = UserRequest::where('request_id', $request->id)->where('admin_service', $request->admin_service)->where('company_id', $company_id)->first();

        if($user_request != null) {
            $chat=Chat::where('admin_service', $request->admin_service)->where('request_id', $request->id)->where('company_id', $company_id)->first();


            if($chat != null) {
                $data = $chat->data;
                $data[] = ['type' => $request->type, 'user' => $request->user_name, 'provider' => $request->provider_name, 'message' => $request->message  ];
                $chat->data = json_encode($data);
                $chat->save();
            } else {
                $chat = new Chat();
                $data[] = ['type' => $request->type, 'user' => $request->user_name, 'provider' => $request->provider_name, 'message' => $request->message  ];
                $chat->admin_service = $request->admin_service;
                $chat->request_id = $request->id;
                $chat->company_id = $company_id;
                $chat->data = json_encode($data);
                $chat->save();
            }

            if($request->type == 'user') {
                (new SendPushNotification)->ChatPushProvider($user_request->provider_id, 'chat_'.strtolower($chat->admin_service),$request->message); 
            } else if($request->type == 'provider') {
                (new SendPushNotification)->ChatPushUser($user_request->user_id, 'chat_'.strtolower($chat->admin_service),$request->message); 
            }
            
            

            return Helper::getResponse(['message' => 'Successfully Inserted!']);
        } else {
            return Helper::getResponse(['status' => 400, 'message' => 'No service found!']);
        }

        
    }

    public function AdminServices()
    {
        $admin_service = Menu::where('company_id', 1)->whereNotIn('admin_service', ['ORDER'] )->where('status', 1)->get();
        return Helper::getResponse(['status' => 200, 'data' => $admin_service]);
    }

}
