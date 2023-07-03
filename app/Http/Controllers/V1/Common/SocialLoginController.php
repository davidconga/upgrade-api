<?php

namespace App\Http\Controllers\V1\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\Common\Setting;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;

use Socialite;
use Auth;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\Common\Country;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCountry;
use App\Models\Common\User;
use App\Models\Common\Provider;
use App\Models\Common\AuthLog;
use App\Models\Common\ProviderService;
use App\Http\Controllers\Resource\ReferralResource;
use Davibennun\LaravelPushNotification\Facades\PushNotification;


class SocialLoginController extends Controller
{
	

    use Encryptable;
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function handleSocialLogin(Request $request, $provider) { 

		$this->validate($request, [
			'social_unique_id' => 'required',
			'device_type' => 'in:ANDROID,IOS',
			'login_by' => 'in:GOOGLE,FACEBOOK,APPLE',
			'salt_key' => 'required',
		]);

		$company_id = base64_decode($request->salt_key);

		$setting = Setting::where('company_id', $company_id )->first();

		$settings = json_decode(json_encode($setting->settings_data));
        $siteConfig = $settings->site;
        $transportConfig = $settings->transport;

		if($provider == 'user')  {
			$user = User::where('social_unique_id', $request->social_unique_id)->first();
			if($user){

				$token = Auth::guard('user')->login($user);

				AuthLog::create(['user_type' => 'User', 'user_id' => $user->id, 'type' => 'login', 'data' => json_encode(
					['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
					'host' => $request->getHost(), 
					'ip' => $request->getClientIp(), 
					'user_agent' => $request->userAgent(), 
					'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
				)]);

				return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $user]]);
			}else{

				if(!$request->has('city_id')) {
					$validators = [];
					$req = array_keys( json_decode( json_encode($request->all()), true ) ); 

					if(!in_array('email', $req )) {
						$validators[] = 'email';
					}
					if(!in_array('mobile', $req )) {
						$validators[] = 'mobile';
					}
					if(!in_array('country_code', $req )) {
						$validators[] = 'country_code';
					}
					if(!in_array('gender', $req )) {
						$validators[] = 'gender';
					}
					if(!in_array('country_id', $req )) {
						$validators[] = 'country_id';
					}
					if(!in_array('city_id', $req )) {
						$validators[] = 'city_id';
					}

					//When the user is not available in db, mobile device considered it as failure and it will redirect to signup.   Web devices consider it as success and continue to signup
					if($request->has('device_type')) {
						return Helper::getResponse(['status' => 422, 'message' => 'Please signup this account.', 'data' => ['status' => 0, 'validators' => $validators] ]);
					} else {
						return Helper::getResponse(['status' => 200, 'message' => 'Fill all the required details', 'data' => ['status' => 0, 'validators' => $validators] ]);
					}
					
				}

				if($request->has('email')) {
		            $request->merge([
		                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
		            ]);
		        }

		        if($request->has('mobile')) {
		            $request->merge([
		                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET'))            
		            ]);
		        }

				$this->validate($request, [
					'email' => 'required',
					'mobile' => 'required',
					'country_code' => 'required',
					'gender' => 'required',
					'country_id' => 'required',
					'city_id' => 'required',
				]);

				$email = $request->email;

				$mobile = $request->mobile;

				$country_code = $request->country_code;

				$this->validate($request, [          
					'email' =>[ Rule::unique('users')->where(function ($query) use($email,$company_id) {
									return $query->where('email', $email)->where('company_id', $company_id);
								 }),
							   ],
					'mobile' =>[ Rule::unique('users')->where(function ($query) use($mobile,$company_id,$country_code) {
									return $query->where('mobile', $mobile)->where('country_code', $country_code)->where('company_id', $company_id);
								 }),
							   ],
				]);

				$request->merge([
		            'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
		            'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
		        ]);
				
				$city = CompanyCity::where('city_id', $request->city_id)->first();

				$country = CompanyCountry::where('company_id',$company_id)->where('country_id', $request->country_id)->first();

        		//$country = Country::find($request->country_id);

				$user=new User();
				$user->company_id=$company_id;
				$user->social_unique_id=$request->social_unique_id;
				$user->first_name=$request->first_name;
				$user->last_name= $request->last_name;
				$user->country_code=$request->country_code;
				$user->mobile=$request->mobile;
				$user->email=$request->email;
        		$user->password = Hash::make($request->social_unique_id);
				$user->gender=$request->gender;
				$user->device_type = $request->device_type;
				$user->device_token = $request->device_token;
				$user->login_by = $request->login_by;
				$user->country_id = $request->country_id;
				$user->state_id = $city->state_id;
				$user->city_id = $request->city_id;
        		$user->currency_symbol = $country->currency;

				if($request->picture != null) {
					$fileContents = file_get_contents($request->picture);

					if($fileContents !== false) {
						$filePath = '/storage/app/public/'.$company_id.'/provider/profile/';

						if (!file_exists( app()->basePath( $filePath )  )) {
				            mkdir(app()->basePath( $filePath ), 0777, true);
				        }

						File::put(app()->basePath() . $filePath . $request->social_unique_id . ".jpg", $fileContents);

						//To show picture 
						$picture = url(). '/storage/'.$company_id.'/provider/profile/' . $request->social_unique_id . ".jpg";
						$user->picture=$picture;
					}
				}

				$user->save();

				AuthLog::create(['user_type' => 'User', 'user_id' => $user->id, 'type' => 'login', 'data' => json_encode(
					['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
					'host' => $request->getHost(), 
					'ip' => $request->getClientIp(), 
					'user_agent' => $request->userAgent(), 
					'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
				)]);

				$newUser = User::find($user->id);

				if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
		            // send welcome email here
		            Helper::siteRegisterMail($newUser);
		        }

				if($newUser->email) {
					$credentials = ['email' => $this->cusencrypt($newUser->email,env('DB_SECRET')), 'password'  => $newUser->social_unique_id, 'company_id'  => $newUser->company_id];
	                if (! $token = Auth::guard('user')->attempt($credentials) ) {
	                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
	                }
	            } else {
	            	$credentials = ['country_code' => $newUser->country_code, 'mobile'  => $this->cusencrypt($newUser->mobile,env('DB_SECRET')), 'password'  => $newUser->social_unique_id, 'company_id'  => $newUser->company_id];
	                if (! $token = Auth::guard('user')->attempt($credentials) ) {
	                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
	                }
	            }

				return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
			}
			
		} else if($provider == 'provider') {
			$user = Provider::where('social_unique_id', $request->social_unique_id)->first();
			if($user){

				$token = Auth::guard('user')->login($user);

				AuthLog::create(['user_type' => 'Provider', 'user_id' => $user->id, 'type' => 'login', 'data' => json_encode(
					['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
					'host' => $request->getHost(), 
					'ip' => $request->getClientIp(), 
					'user_agent' => $request->userAgent(), 
					'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
				)]);

				return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $user]]);
			}else{   

				if(!$request->has('city_id')) {
					$validators = [];
					$req = array_keys( json_decode( json_encode($request->all()), true ) ); 

					if(!in_array('email', $req )) {
						$validators[] = 'email';
					}
					if(!in_array('mobile', $req )) {
						$validators[] = 'mobile';
					}
					if(!in_array('country_code', $req )) {
						$validators[] = 'country_code';
					}
					if(!in_array('gender', $req )) {
						$validators[] = 'gender';
					}
					if(!in_array('country_id', $req )) {
						$validators[] = 'country_id';
					}
					if(!in_array('city_id', $req )) {
						$validators[] = 'city_id';
					}

					if($request->has('device_type')) {
						return Helper::getResponse(['status' => 422, 'message' => 'Please signup this account.', 'data' => ['status' => 0, 'validators' => $validators] ]);
					} else {
						return Helper::getResponse(['status' => 200, 'message' => 'Fill all the required details', 'data' => ['status' => 0, 'validators' => $validators] ]);
					}

					
				}

				

				if($request->has('email')) {
		            $request->merge([
		                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
		            ]);
		        }

		        if($request->has('mobile')) {
		            $request->merge([
		                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET'))            
		            ]);
		        }

		        $this->validate($request, [
					'email' => 'required',
					'mobile' => 'required',
					'country_code' => 'required',
					'gender' => 'required',
					'country_id' => 'required',
					'city_id' => 'required',
				]);

				$email = $request->email;

				$mobile = $request->mobile;

				$country_code = $request->country_code;

				$this->validate($request, [          
					'email' =>[ Rule::unique('providers')->where(function ($query) use($email,$company_id) {
									return $query->where('email', $email)->where('company_id', $company_id);
								 }),
							   ],
					'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id,$country_code) {
									return $query->where('mobile', $mobile)->where('country_code', $country_code)->where('company_id', $company_id);
								 }),
							   ],
				]);

				$request->merge([
		            'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
		            'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
		        ]);

				$city = CompanyCity::where('city_id', $request->city_id)->first();

        		$country = CompanyCountry::where('company_id',$company_id)->where('country_id', $request->country_id)->first();

				$user=new Provider();
				$user->company_id=$company_id;
				$user->social_unique_id=$request->social_unique_id;
				$user->first_name=$request->first_name;
				$user->last_name= $request->last_name;
				$user->country_code=$request->country_code;
				$user->mobile=$request->mobile;
				$user->email=$request->email;
        		$user->password = Hash::make($request->social_unique_id);
				$user->gender=$request->gender;
				$user->device_type = $request->device_type;
				$user->device_token = $request->device_token;
				$user->login_by = $request->login_by;
				$user->country_id = $request->country_id;
				$user->state_id = $city->state_id;
				$user->city_id = $request->city_id;
        		$user->currency_symbol = $country->currency;

				if($request->picture != null) {
					$fileContents = file_get_contents($request->picture);

					if($fileContents !== false) {
						$filePath = '/storage/app/public/'.$company_id.'/provider/profile/';

						if (!file_exists( app()->basePath( $filePath )  )) {
				            mkdir(app()->basePath( $filePath ), 0777, true);
				        }

						File::put(app()->basePath() . $filePath . $request->social_unique_id . ".jpg", $fileContents);

						//To show picture 
						$picture = url(). '/storage/'.$company_id.'/provider/profile/' . $request->social_unique_id . ".jpg";
						$user->picture=$picture;
					}
				}

				$user->save();

				if($setting->demo_mode == 1) {
					$user->status = "APPROVED";
				}

				AuthLog::create(['user_type' => 'Provider', 'user_id' => $user->id, 'type' => 'login', 'data' => json_encode(
					['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
					'host' => $request->getHost(), 
					'ip' => $request->getClientIp(), 
					'user_agent' => $request->userAgent(), 
					'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
				)]);

				$newUser = Provider::find($user->id);

				if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
		            // send welcome email here
		            Helper::siteRegisterMail($newUser);
		        }

				if($newUser->email) {
					$credentials = ['email' => $this->cusencrypt($newUser->email,env('DB_SECRET')), 'password'  => $newUser->social_unique_id, 'company_id'  => $newUser->company_id];

	                if (! $token = Auth::guard('provider')->attempt($credentials) ) {
	                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
	                }
	            } else {
	            	$credentials = ['country_code' => $newUser->country_code, 'mobile'  => $this->cusencrypt($newUser->mobile,env('DB_SECRET')), 'password'  => $newUser->social_unique_id, 'company_id'  => $newUser->company_id];
	                if (! $token = Auth::guard('provider')->attempt($credentials) ) {
	                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
	                }
	            }

				return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
			}
			
		}
	}

	public function push(Request $request, $type) {

		$settings = Setting::where('company_id', $request->company_id)->first();

		if($type == 'android') {

			$config = [
				'environment' => $settings->settings_data->site->environment,
				'apiKey'      => $settings->settings_data->site->android_push_key,
				'service'     => 'gcm'
			];

		} else if($type == 'ios') {

			$config = [
				'environment' => $settings->settings_data->site->environment,
				'certificate' => app()->basePath('storage/app/public/'.$request->company_id.'/apns' ).'/user.pem',
				'passPhrase'  => $settings->settings_data->site->ios_push_password,
				'service'     => 'apns'
			];

		}

		$message = \PushNotification::Message('Hi Test Push', array(
                'badge' => 1,
                'sound' => 'default',
                'custom' => array('type' => $type)
            ));

		$data = PushNotification::app($config)->to($request->token)
		->send($message);
			dd($data);
	}

}


//type = common, transport, order, service, approval, notification



