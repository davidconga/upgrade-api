<?php

namespace App\Http\Controllers\V1\Common\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Common\Setting;
use App\Models\Common\UserAddress;
use App\Models\Common\Menu;
use App\Models\Common\User;
use App\Models\Common\Card;
use App\Models\Common\UserWallet;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\CompanyCountry; 
use App\Models\Common\CompanyCity;
use App\Models\Common\City;
use App\Models\Common\State;
use App\Models\Common\Reason;
use App\Models\Common\Notifications;
use App\Models\Common\Promocode;
use App\Models\Common\UserRequest;
use App\Models\Common\AdminService;
use App\Models\Common\Chat;
use App\Helpers\Helper;
use Carbon\Carbon;
use Auth;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Services\ReferralResource;

class HomeController extends Controller
{
	use Encryptable;
	public function index(Request $request) {
		$user = Auth::guard('user')->user();


        $company_id = $user ? $user->company_id : 1;

        $city_id = $user ? $user->city_id : $request->city_id;

        $menus= new \stdClass;
        $recent=array();

        try{
            if($user){
              $recent_servicerequest=\App\Models\Service\ServiceRequest::select('service_category_id')->where('user_id',$user->id)->groupBy('service_category_id')->orderby('id','DESC')->limit(5)->get();
          }else{
            $recent_servicerequest=array(); 
          }
               $recent_data=array();

              if(count($recent_servicerequest) > 0){
                foreach ($recent_servicerequest as $key => $value) {
                   $recent_data[$key]=$value->service_category_id;
                }
                $recent=Menu::whereIn('menu_type_id',$recent_data)->where('company_id', $company_id)->where('status',1)->orderby('sort_order')->get();
              }else{
                $recent=array();   
              }
          
        } catch (Exception $e) {
                $recent=array();
        }
        

        $menus->services = Menu::with('service')->whereHas('cities', function ($query) use($user,$city_id) {
            if($city_id != 0){
			$query->where('city_id', $city_id);
            }
            $query->where('status',1);
		})->where('company_id', $company_id)->where('status',1)->orderby('sort_order')->get();
        $menus->promocodes = Promocode::where('company_id', $company_id)
                    ->where('expiration','>=',date("Y-m-d H:i"))
                    ->whereDoesntHave('promousage', function($query) use($user) {
                                $query->where('user_id',$user ? $user->id : 0);
                            })
                    ->get();
        $menus->recent_requests = $recent;           

        return Helper::getResponse(['data' => $menus]);
	}

    public function ongoing_services(Request $request) {

        try{
            $requests = UserRequest::with('service')->where('user_id', Auth::guard('user')->user()->id )->whereNotIn('status', ['SCHEDULED', 'CANCELLED'])->get();

            return Helper::getResponse(['data' => $requests]);
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    public function addmanageaddress(Request $request){

        $this->validate($request, [
            'map_address' => 'required',
            'address_type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'flat_no' => 'required',
            'street' => 'required',
        ]);

        try{

            $title= ($request->address_type=='Home' || $request->address_type=='Work') ? $request->address_type : ( (!empty($request->title)) ? $request->title : "Other");
            

            $UserAddress =UserAddress::where('company_id', Auth::guard('user')->user()->company_id)->where('user_id',Auth::guard('user')->user()->id)->where('address_type',$request->address_type)->where('title',$title)->first();

            if($UserAddress != null){
                //return Helper::getResponse(['status' => 404, 'message' => 'Address Type Already Exist']);   
                $useraddress = $UserAddress;
            }else{
                $useraddress = new UserAddress;
            }





            /*if($request->address_type=='Home' || $request->address_type=='Work' ){
                $UserAddress=$UserAddress->first();

                if($UserAddress != null){
                    //return Helper::getResponse(['status' => 404, 'message' => 'Address Type Already Exist']);   
                    $useraddress = $UserAddress;
                }else{
                    $useraddress = new UserAddress;
                }
                $title=$request->address_type;
            }else{
                $title= (!empty($request->title)) ? $request->title : "Other";
                $UserAddress=$UserAddress->where('title',$request->title)->first();

                $useraddress = new UserAddress;
            }*/
            
            $useraddress->address_type=$request->address_type;
            $useraddress->company_id=Auth::guard('user')->user()->company_id;
            $useraddress->user_id=Auth::guard('user')->user()->id;
            $useraddress->landmark=$request->landmark;
            $useraddress->flat_no=$request->flat_no;
            $useraddress->title=$title;
            $useraddress->street=$request->street;
            $useraddress->latitude=$request->latitude;
            $useraddress->longitude=$request->longitude;
            $useraddress->map_address=$request->map_address;
            $useraddress->city=$request->city;
            $useraddress->state=$request->state;
            $useraddress->county=$request->county;
            $useraddress->zipcode=$request->zipcode;
            $useraddress->save();
             return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        }
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function editmanageaddress(Request $request,$id){
             $useraddress_details=UserAddress::find($id);
            return Helper::getResponse(['status' => 200,'data' => $useraddress_details]);
    }
    public function updatemanageaddress(Request $request){
     
     $this->validate($request, [
            
            'address_type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'flat_no' => 'required',
            'street' => 'required',
            
           
           
        ]);
    try{

       if($request->address_type=='Home' || $request->address_type=='Work' ){

          $UserAddress =UserAddress::where('company_id', Auth::guard('user')->user()->company_id)->where('user_id',Auth::guard('user')->user()->id)->where('address_type',$request->address_type)->where('id','!=',$request->id)->get();

          if(count($UserAddress) > 0){
          	//return Helper::getResponse(['status' => 404, 'message' => 'Address Type Already Exist']);
          }
           $title=null;

       } else{

            $title= (!empty($request->title)) ? $request->title : "Other";
          }
	        $useraddress= UserAddress::findOrFail($request->id);
            $useraddress->address_type=$request->address_type;
            $useraddress->landmark=$request->landmark;
            $useraddress->flat_no=$request->flat_no;
            $useraddress->street=$request->street;
            $useraddress->latitude=$request->latitude;
            $useraddress->longitude=$request->longitude;
            $useraddress->city=$request->city;
            $useraddress->state=$request->state;
            $useraddress->county=$request->county;
            $useraddress->zipcode=$request->zipcode;
            $useraddress->map_address=$request->map_address;
            $useraddress->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
     }
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function listmanageaddress(Request $request){
        try{
            $useraddress_details=UserAddress::where('user_id',Auth::guard('user')->user()->id)->get();
            return Helper::getResponse(['status' => 200,'data' => $useraddress_details]);
        }
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function deletemanageaddress($id){
        try{
         $useraddress_details=UserAddress::where('id',$id)->delete(); ;
         return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
    
        }
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


     public function updatelanguage(Request $request){
     
     $this->validate($request, [
            'language' => 'required',
        ]);
    try{
            $user= User::findOrFail(Auth::guard('user')->user()->id);
            $user->language=$request->language;
             $user->save();
    return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
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
	public function show_profile()
	{
		$user_details = User::with('country','state','city')->where('id',Auth::guard('user')->user()->id)->where('company_id',Auth::guard('user')->user()->company_id)->first();
        $user_details['referral']=(object)array();
   
		$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('user')->user()->company_id)->first()->settings_data));
		if($settings->site->referral==1){
             $user_details['referral']->referral_code=$user_details['referral_unique_id'];
			 $user_details['referral']->referral_amount=(double)$settings->site->referral_amount;
			 $user_details['referral']->referral_count=(int)$settings->site->referral_count;
			 $user_details['referral']->user_referral_count = (new ReferralResource)->get_referral(1, Auth::guard('user')->user()->id)[0]->total_count;
				$user_details['referral']->user_referral_amount = (new ReferralResource)->get_referral(1, Auth::guard('user')->user()->id)[0]->total_amount;
		}
        return Helper::getResponse(['data' => $user_details]);
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
                 $company_id=Auth::guard('user')->user()->company_id;
                 $id=Auth::guard('user')->user()->id;

               $this->validate($request, [          
               'mobile' =>[ Rule::unique('users')->where(function ($query) use($mobile,$company_id,$id) {
                            return $query->where('mobile', $mobile)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                         }),
                       ],
              ]);

             $request->merge([
                'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
            ]); 

			}

            


		try{
            $user = User::where('id',Auth::guard('user')->user()->id)->where('company_id',Auth::guard('user')->user()->company_id)->first();
			$user->first_name = $request->first_name;
			if($request->has('last_name')) {
				$user->last_name = $request->last_name;
			}
			if($request->has('email')) {
				$user->email = $request->email;
			}

			if($request->has('language')) {
				$user->language = $request->language;
			}
			if($request->has('mobile')) {
				$user->mobile = $request->mobile;
			}
			
			if($request->has('city_id')) {
				$user->city_id = $request->city_id;
			}
			if($request->has('country_code')) {
				$user->country_code = $request->country_code;
			}
			if($request->has('gender')) {
               $user->gender = $request->gender;
			}
			if($request->hasFile('picture')) {
                $user->picture = Helper::upload_file($request->file('picture'), 'user', null, Auth::guard('user')->user()->company_id);
			}
			$user->save();
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update'), 'data' => $user]);
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

			$User =User::where('id',Auth::guard('user')->user()->id)->where('company_id',Auth::guard('user')->user()->company_id)->first();
			if(password_verify($request->old_password, $User->password))
			{
				$User->password = Hash::make($request->password);
				$User->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            }else{
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

            $user = Auth::guard('user')->user();

            $exist = Card::where('user_id', $user->id)
                            ->where('last_four',$card->last4)
                            ->where('brand',$card->brand)
                            ->count();

            if($exist == 0){
                $create_card = new Card;
                $create_card->user_id = $user->id;
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

            return Helper::getResponse(['status' => 200, 'data'=> $create_card, 'message' => trans('api.card_added')]); 

        } catch(Exception $e){
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}
	
	public function carddetail(Request $request)
	{
		$cards = Card::where('user_id',Auth::guard('user')->user()->id)->where('company_id',Auth::guard('user')->user()->company_id)->get();
		return Helper::getResponse(['data' => $cards]);	
	}

	public function deleteCard(Request $request,$id)
    {
        $card = Card::where('id', $id)->first();
        if($card){
            try {
		        Card::where('id',$id)->delete();
                return Helper::getResponse(['status' => 200, 'message' => 'Card Deleted']);
            }catch (Exception $e) {
				return Helper::getResponse(['status' => 422, 'message' => 'Card Not Found', 'error' => $e->getMessage()]);
			}
        }else{
            return Helper::getResponse(['status' => 422, 'message' => 'Card Not Found']);
        }
	}

	/**
     * setting stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function set_stripe(){

        $settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('user')->user()->company_id)->first()->settings_data));

        $paymentConfig = json_decode( json_encode( $settings->payment ) , true);

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

    /**
     * Get a stripe customer id.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_id()
    {
        if(Auth::guard('user')->user()->stripe_cust_id != null){
            return Auth::guard('user')->user()->stripe_cust_id;
        }else{

            try{ 
                $stripe = $this->set_stripe();
                $customer = \Stripe\Customer::create([
                    'email' => Auth::guard('user')->user()->email,
                ]);

                User::where('id',Auth::guard('user')->user()->id)->update(['stripe_cust_id' => $customer['id']]);
                return $customer['id'];

            } catch(Exception $e){
                return $e;
            }
        }
	}
	public function userlist(){

		$user_list = User::where('id',Auth::guard('user')->user()->id)->where('company_id',Auth::guard('user')->user()->company_id)->with('country')->first();
		return Helper::getResponse(['data' => $user_list]);
	}
	public function walletlist(Request $request)
	{
        if($request->has('limit')) {
            $user_wallet = UserWallet::select('id', 'transaction_id', 'transaction_desc','transaction_alias', 'type', 'amount','created_at')->with(['payment_log' => function($query){  $query->select('id','company_id','is_wallet','user_type','payment_mode','user_id','amount','transaction_code'); }])
            ->where('company_id',Auth::guard('user')->user()->company_id)->where('user_id',Auth::guard('user')->user()->id)->orderBy('id','desc');
            $totalRecords = $user_wallet->count();
            $user_wallet = $user_wallet->take($request->limit)->offset($request->offset)->get();
            $response['total_records'] = $totalRecords;
            $response['data'] = $user_wallet;
            return Helper::getResponse(['data' => $response]);
        } else {
            $user_wallet = UserWallet::select('id','user_id', 'transaction_id','transaction_alias', 'transaction_desc', 'type', 'amount','created_at')->with(['payment_log' => function($query){  $query->select('id','company_id','is_wallet','user_type','payment_mode','user_id','amount','transaction_code'); },'user'=>function($query){
                $query->select('id','currency_symbol');
            }])->where('company_id',Auth::guard('user')->user()->company_id)->where('user_id',Auth::guard('user')->user()->id);
                    if($request->has('search_text') && $request->search_text != null) {
                        $user_wallet->Search($request->search_text);
                    }

                    if($request->has('order_by')) {
                        $user_wallet->orderby($request->order_by, $request->order_direction);
                    }
                    $user_wallet=$user_wallet->paginate(10); 
        }
        return Helper::getResponse(['data' => $user_wallet]);
	}
	public function order_status(Request $request){ 

		$order_status = UserRequest::where('user_id',Auth::guard('user')->user()->id)
						->whereNotIn('status',['CANCELLED','SCHEDULED'])->get();
		return Helper::getResponse(['data' => $order_status]);
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

    public function cities(Request $request)
    {
        $company_cities = CompanyCity::where('company_id',\Auth::guard('user')->user()->company_id)->where('country_id',\Auth::guard('user')->user()->country_id)->where('status',1)->pluck('city_id')->all();


        $cities = City::whereIn('id',$company_cities)->get();
        return Helper::getResponse(['data' => $cities]);
    }

    public function promocode(Request $request)
    {

        $promocode = Promocode::where('company_id',\Auth::guard('user')->user()->company_id)->whereDate('expiration','>=',Carbon::today())->orderby('id','desc')->get();
       
        return Helper::getResponse(['data' => $promocode]);
    }

    public function reasons(Request $request)
    {
        $reason = Reason::where('company_id', Auth::guard('user')->user()->company_id)->where('service', $request->type)
                    ->where('type', 'USER')
                    ->where('status','active')
                    ->get();

        return Helper::getResponse(['data' => $reason]);
    }

      public function notification(Request $request)
    {
        try{
             $timezone=(Auth::guard('user')->user()->state_id) ? State::find(Auth::guard('user')->user()->state_id)->timezone : '';
            $jsonResponse = [];
            if($request->has('limit')) {
                $notifications = Notifications::where('company_id', Auth::guard('user')->user()->company_id)
                                ->where('notify_type','!=', "provider")->where('status','active')
                                ->whereDate('expiry_date','>=',Carbon::today())
                                ->take($request->limit)->offset($request->offset)->orderby('id','desc')->get();
            }else{
                $notifications = Notifications::where('company_id', Auth::guard('user')->user()->company_id)->where('notify_type','!=', "provider")->where('status','active')->whereDate('expiry_date','>=',Carbon::today())->orderby('id','desc')->paginate(10); 
            }
            
            if(count($notifications) > 0){
                foreach($notifications as $k=>$val){
                  $notifications[$k]['created_at']=(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$val['created_at'], 'UTC'))->setTimezone($timezone)->format('Y-m-d H:i:s');    
                } 
           } 



            $jsonResponse['total_records'] = count($notifications);
            $jsonResponse['notification'] = $notifications;
        }catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
        return Helper::getResponse(['data' => $jsonResponse]);
    }
	
	public function search_user(Request $request){
		$results=array();
		$term =  $request->input('stext');  
        $queries = User::where('company_id', Auth::user()->company_id)
                    ->where(function ($query) use($term) {
                        $query->where('first_name', 'LIKE', $term.'%')
                            ->orWhere('last_name', 'LIKE', $term.'%');
                    })->take(5)->get();
		foreach ($queries as $query)
		{
			$results[]=$query;
		}    
		return response()->json(array('success' => true, 'data'=>$results));
    }
    public function city(Request $request)
    {
        try{ 
                $city_update = User::where('company_id', Auth::guard('user')->user()->company_id)
                            ->where('id', Auth::guard('user')->user()->id)
                            ->update(['city_id' => $request['city_id']]);
                return Helper::getResponse(['status' => 200, 'message' => 'Updated Successfully']);
            }catch (\Throwable $e) {
                    return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }  
    }

    public function defaultcard(Request $request)
    {  
      try{ 
           $card=Card::where('card_id',$request->card_id)->get();
            if(count($card) > 0){   
                Card::where('user_id',Auth::guard('user')->user()->id)->update(['is_default'=>0]);
                Card::where('card_id',$request->card_id)->update(['is_default'=>1]);

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            }else{
                return Helper::getResponse(['status' => 200, 'message' => "Card Not Exist"]);
            } 

        }
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function listpromocode($service=null) {
        $type=strtoupper($service);
        
        $promocodes = Promocode::where('company_id', Auth::guard('user')->user()->company_id)->where('service',$type)->get();

        return Helper::getResponse(['data' => $promocodes]);
    }

    public function get_chat(Request $request) {

        $this->validate($request,[
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
            'id' => 'required', 
        ]);

        $chat=Chat::where('admin_service', $request->admin_service)->where('request_id', $request->id)->where('company_id', Auth::guard('user')->user()->company_id)->get();

        return Helper::getResponse(['data' => $chat]);
    }

    public function updateDeviceToken(Request $request){
        $this->validate($request,[
            'device_token' => 'required'
        ]);
        try{
            $company_id = Auth::guard('user')->user()->company_id;
            $user_id = Auth::guard('user')->user()->id;
            $update = User::where('id',$user_id)->update(['device_token'=>$request->device_token]);
            if($update){
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            }else{
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
        }catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 500,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
	
}
