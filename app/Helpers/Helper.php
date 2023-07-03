<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Common\RequestLog;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use App\Models\Common\AdminService;
use App\Models\Common\Setting;
use Auth;
use Illuminate\Support\Facades\Crypt; 
use Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Mail;

class Helper {

	public static function getUsername(Request $request) {
		
		$username = "";

		if(isset($request->mobile)) {
			$username = 'mobile';
		} else if(isset($request->email)) {
			$username = 'email';
		}

		return $username;
	}

	public static function currencyFormat($value = '',$symbol='')
	{
		if($value == ""){
			return $symbol.number_format(0, 2, '.', '');
		} else {
			return $symbol.number_format($value, 2, '.', '');
		}
	}

	public static function decimalRoundOff($value)
	{
		return number_format($value, 2, '.', '');
	}

	public static function qrCode($data, $file, $company_id, $path = 'qr_code/', $size = 500, $margin = 10) {
		return true;

		$qrCode = new QrCode();
        $qrCode->setText($data);
        $qrCode->setSize($size);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin($margin);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));

        $qrCode->setRoundBlockSize(true);
        $qrCode->setValidateResult(false);
        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);
        $filePath = 'app/public/'.$company_id.'/'.$path;
		
		$filePath = 'app/public/'.$company_id.'/'.$path;

        if (!file_exists( app()->basePath('storage/'.$filePath )  )) {
            mkdir(app()->basePath('storage/'.$filePath ), 0777, true);
        }

        $qrCode->writeFile( app()->basePath('storage/'.$filePath ).$file);

        return url().'/storage/'.$company_id.'/'.$path.$file; 

	}

	public static function upload_file($picture, $path, $file = null, $company_id = null)
	{
		if($file == null) {
			$file_name = time();
			$file_name .= rand();
			$file_name = sha1($file_name);

			$file = $file_name.'.'.$picture->getClientOriginalExtension();
		}
		
		if(!empty(Auth::user())){          
            $company_id = Auth::user()->company_id;
        }

		$path = $company_id.'/'.$path;
		
		if (!file_exists( app()->basePath('storage/app/public/'.$path )  )) {
            mkdir(app()->basePath('storage/app/public/'.$path ), 0777, true);
        }

        return url().'/storage/'.$picture->storeAs($path, $file);
	}

	public static function upload_providerfile($picture, $path, $file = null, $company_id = null)
	{
		if($file == null) {
			$file_name = time();
			$file_name .= rand();
			$file_name = sha1($file_name);

			$file = $file_name.'.'.$picture->getClientOriginalExtension();
		}

		$path = ( ($company_id == null) ? Auth::guard('provider')->user()->company_id : $company_id ) .'/'.$path;
		
		if (!file_exists( app()->basePath('storage/app/public/'.$path )  )) {
            mkdir(app()->basePath('storage/app/public/'.$path ), 0777, true);
        }

        return url().'/storage/'.$picture->storeAs($path, $file);
	}

	public static function getGuard(){
	    if(Auth::guard('admin')->check()) {
	    	return strtoupper("admin");
	    } else if(Auth::guard('provider')->check()) {
	    	return strtoupper("provider");
	    } else if(Auth::guard('user')->check()) {
	    	return strtoupper("user");
	    } else if(Auth::guard('shop')->check()){
	    	return strtoupper("shop");
	    }
	}

	public static function curl($url)
	{
		// return $url;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $return = curl_exec($ch);
	    curl_close ($ch);
	    return $return;


		// $curl = curl_init();

		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => $url,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => "",
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 30,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => "GET",
		// 	CURLOPT_HTTPHEADER => array(
		// 	"cache-control: no-cache",
		// 	),
		// ));

		// $response = curl_exec($curl);
		// $err = curl_error($curl);
		// curl_close($curl);

		// return $response;
	}

	public static function generate_booking_id($prefix) {
		return $prefix.mt_rand(100000, 999999);
	}

	public static function setting($company_id = null)
	{
		$id = ($company_id == null) ? Auth::guard(strtolower(self::getGuard()))->user()->company_id : $company_id;
		$setting = Setting::where('company_id', $id )->first();
		$settings = json_decode(json_encode($setting->settings_data));
		$settings->demo_mode = $setting->demo_mode;
		return $settings;
	}

	public static function getAddress($latitude,$longitude){

		if(!empty($latitude) && !empty($longitude)){
			//Send request and receive json data by address
			$geocodeFromLatLong = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false&key='.config('constants.map_key')); 
			$output = getDistanceMap(trim($latitude), trim($longitude));
			$status = $output->status;
			//Get address from json data
			$address = ($status=="OK")?$output->results[0]->formatted_address:'';
			//Return address of the given latitude and longitude
			if(!empty($address)){
				return $address;
			}else{
				return false;
			}
		}else{
			return false;   
		}
	}

	public static function getDistanceMap($source, $destination) {

		$settings = Helper::setting();
		$siteConfig = $settings->site;

		$map = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.implode('|', $source).'&destinations='.implode('|', $destination).'&sensor=false&key='.$siteConfig->server_key); 
		return json_decode($map);
	}

	public static function my_encrypt($passphrase, $encrypt) {
	 
	    $salt = openssl_random_pseudo_bytes(128);
		$iv = openssl_random_pseudo_bytes(16);
		//on PHP7 can use random_bytes() istead openssl_random_pseudo_bytes()
		//or PHP5x see : https://github.com/paragonie/random_compat

		$iterations = 999;  
		$key = hash_pbkdf2("sha1", $passphrase, $salt, $iterations, 64);

		$encrypted_data = openssl_encrypt($encrypt, 'aes-128-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

		$data = array("ciphertext" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "salt" => bin2hex($salt));

		return $data;

	}

	public static function encryptResponse($response = []) {

		$status = !empty($response['status']) ? $response['status'] : 200 ;
		$title = !empty($response['title']) ? $response['title'] : self::getStatus($status) ;
		$message = !empty($response['message']) ? $response['message'] : '' ;
		$responseData = !empty($response['data']) ? self::my_encrypt('FbcCY2yCFBwVCUE9R+6kJ4fAL4BJxxjd', json_encode($response['data'])) : [] ;
		$error = !empty($response['error']) ? $response['error'] : [] ;

		if( ($status != 401) && ($status != 405) && ($status != 422)  ) {

			RequestLog::create(['data' => json_encode([
			'request' => app('request')->request->all(),
			'response' => $message,
			'error' => $error,
			'responseCode' => $status,
			$_SERVER['REQUEST_METHOD'] => $_SERVER['REQUEST_URI'] . " " . $_SERVER['SERVER_PROTOCOL'], 
            'host' => $_SERVER['HTTP_HOST'], 
            'ip' => $_SERVER['REMOTE_ADDR'], 
            'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')])]);

		}
		
		return response()->json(['statusCode' => (string) $status, 'title' => $title, 'message' => $message, 'responseData' => $responseData, 'error' => $error], $status);
	}

	public static function getResponse($response = []) {
		
		$status = !empty($response['status']) ? $response['status'] : 200 ;
		$title = !empty($response['title']) ? $response['title'] : self::getStatus($status) ;
		$message = !empty($response['message']) ? $response['message'] : '' ;
		$responseData = !empty($response['data']) ? $response['data'] : [] ;
		$error = !empty($response['error']) ? $response['error'] : [] ;

		if( ($status != 401) && ($status != 405) && ($status != 422)  ) {
		
			app('request')->request->remove('picture');
			app('request')->request->remove('file');
			app('request')->request->remove('vehicle_image');
			app('request')->request->remove('vehicle_marker');

			RequestLog::create(['data' => json_encode([
			'request' => app('request')->request->all(),
			'response' => $message,
			'error' => $error,
			'responseCode' => $status,
			$_SERVER['REQUEST_METHOD'] => $_SERVER['REQUEST_URI'] . " " . $_SERVER['SERVER_PROTOCOL'], 
            'host' => $_SERVER['HTTP_HOST'], 
            'ip' => $_SERVER['REMOTE_ADDR'], 
            'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')])]);

		}
		
		return response()->json(['statusCode' => (string) $status, 'title' => $title, 'message' => $message, 'responseData' => $responseData, 'error' => $error], $status);
	}

	public static function getStatus($code) {

		switch ($code) {
			case 200:
				return "OK";
				break;
			
			case 201:
				return "Created";
				break;

			case 204:
				return "No Content";
				break;

			case 301:
				return "Moved Permanently";
				break;

			case 400:
				return "Bad Request";
				break;

			case 401:
				return "Unauthorized";
				break;

			case 403:
				return "Forbidden";
				break;

			case 404:
				return "Not Found";
				break;

			case 405:
				return "Method Not Allowed";
				break;

			case 422:
				return "Unprocessable Entity";
				break;

			case 500:
				return "Internal Server Error";
				break;

			case 502:
				return "Bad Gateway";
				break;

			case 503:
				return "Service Unavailable";
				break;
		}
	}


	public static function delete_picture($picture) {
		$url = app()->basePath('storage/') . $picture;
		@unlink($url);
		return true;
	}

	public static function send_sms($companyId,$plusCodeMobileNumber, $smsMessage) {
		//  SEND OTP TO REGISTER MEMBER
		$settings = json_decode(json_encode(Setting::where('company_id',$companyId)->first()->settings_data));
		$siteConfig = $settings->site; 
		$accountSid =$siteConfig->sms_account_sid;
		$authToken = $siteConfig->sms_auth_token;
		$twilioNumber = $siteConfig->sms_from_number;
		
		$client = new Client($accountSid, $authToken);
		// $tousernumber = '+17577932902';
		$tousernumber = $plusCodeMobileNumber ;
		try {
			$client->messages->create(
				$tousernumber,
				[
					"body" => $smsMessage,
					"from" => $twilioNumber
					//   On US phone numbers, you could send an image as well!
					//  'mediaUrl' => $imageUrl
				]
			);
			Log::info('Message sent to ' . $plusCodeMobileNumber.'from '. $twilioNumber);
			return 1;
		} catch (TwilioException $e) {
			Log::error(
				'Could not send SMS notification.' .
				' Twilio replied with: ' . $e
			);
			return $e;
		}

	}
	public static function sendtextlocal($companyId,$plusCodeMobileNumber, $smsMessage){
		\Log::info('kavi');
		$apiKey = 'tKkL1klgHJQ-vFIUuY71ue08BamFFAXrGZFTNwVy10';
	
	// Message details
	$numbers = array($plusCodeMobileNumber);
	$sender = 'OhYess';
	$message = rawurlencode($smsMessage);
    \Log::info($message);
	$numbers = implode(',', $numbers);
 
	// Prepare data for POST request
	$data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
 
	// Send the POST request with cURL
	$ch = curl_init('https://api.textlocal.in/send/');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	\Log::info($response);
	// Process your response here
	// if($response->status=='success'){
	// 	return 1;
	// }else{
	// 	return $response;
	// }
	return 1;
	// Log::info('Message sent to ' . $plusCodeMobileNumber.'from '. $twilioNumber);
	}

	public static function siteRegisterMail($user){

		$settings = json_decode(json_encode(Setting::where('company_id',$user->company_id)->first()->settings_data));

		Mail::send('mails.welcome', ['user' => $user, 'settings' => $settings], function ($mail) use ($user, $settings) {
			$mail->from($settings->site->mail_from_address, $settings->site->mail_from_name);
			$mail->to($user->email, $user->first_name.' '.$user->last_name)->subject('Welcome');
		});

		return true;
	}
	
	public static function send_emails($templateFile,$toEmail,$subject, $data) {
		try{
			//dd($data['salt_key']);
            if(isset($data['salt_key'])){
				$settings = json_decode(json_encode(Setting::where('company_id',$data['salt_key'])->first()->settings_data));
			}else{
                   if(!empty(Auth::user())){          
			            $company_id = Auth::user()->company_id;
			        }
			        else if(!empty(Auth::guard('shop')->user())){          
			            $company_id = Auth::guard('shop')->user()->company_id;
			        }else{

			        }
				$settings = json_decode(json_encode(Setting::where('company_id',$company_id)->first()->settings_data));
			}
			$data['settings'] = $settings;
			$mail =  Mail::send("$templateFile",$data,function($message) use ($data,$toEmail,$subject,$settings) {
				$message->from($settings->site->mail_from_address, $settings->site->mail_from_name);
				$message->to($toEmail)->subject($subject);
			});
			
			if( count(Mail::failures()) > 0 ) {
			  
			   throw new \Exception('Error: Mail sent failed!');

			} else {
				return true;
			}
			
		}
		catch (\Throwable $e) {	
			dd($e);
		
            throw new \Exception($e->getMessage());
        } 
		
	}

	
	public static function send_emails_job($templateFile, $toEmail, $subject, $data) 
	{
		try{
			
			$mail =  Mail::send($templateFile, $data, function($message) use ($data, $toEmail, $subject) {
				$message->from("dev@appoets.com", "GOX");
				$message->to($toEmail)->subject($subject);
			});

			// dd(Mail::failures());
			
			if( count(Mail::failures()) > 0 ) {
			  
			   throw new \Exception('Error: Mail sent failed!');

			} else {
				return true;
			}
			
		}
		catch (\Throwable $e) {	
			dd($e);
		
            throw new \Exception($e->getMessage());
        } 
		
	}
	public static function dateFormat($company_id=null){
	$setting = Setting::where('company_id', 1)->first();
	$settings = json_decode(json_encode($setting->settings_data));
	$siteConfig = isset($settings->site->date_format) ? $settings->site->date_format:0 ;
		if($siteConfig=='1'){
		         return "d-m-Y H:i:s";
		}else{
		         return "d-m-Y g:i A";
		}
	}

	public static function push_aps($data) {

		$device_token   = $data['token'];
	    $pem_file       = $data['pem'];
	    $pem_secret     = $data['password'];
	    $apns_topic     = $data['topic'];
	    $post = $data['post'];
	    $url = $data['url']."/3/device/$device_token";
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("apns-topic: $apns_topic"));
	    curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
	    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
	    $response = curl_exec($ch);
	    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        \Log::info("ios push");
	    \Log::info($ch);
	    \Log::info($httpcode);
	    
		return $response;

	}
}