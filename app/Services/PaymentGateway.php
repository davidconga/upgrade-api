<?php 

namespace App\Services;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use Exception;
use DateTime;
use Auth;
use Lang;
use App\Models\Common\Setting;
use App\ServiceType;
use App\Models\Common\Promocode;
use App\Provider;
use App\ProviderService;
use App\Helpers\Helper;
use GuzzleHttp\Client;
use App\Models\Common\PaymentLog;


//PayuMoney
use Tzsk\Payu\Facade\Payment AS PayuPayment;

//Paypal
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payee;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

use Redirect;
use Session;
use URL;


class PaymentGateway {

	private $gateway;

	public function __construct($gateway){
		$this->gateway = strtoupper($gateway);
	}

	public function process($attributes) {
		$provider_url = '';

		$gateway = ($this->gateway == 'STRIPE') ? 'CARD' : $this->gateway ;

		$log = PaymentLog::where('transaction_code', $attributes['order'])->where('payment_mode', $gateway )->first();

		if($log->user_type == 'provider') {
			$provider_url = '/provider';
		}

		switch ($this->gateway) {

			case "STRIPE":

				try {
				
					$settings = json_decode(json_encode(Setting::first()->settings_data));
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


        			\Stripe\Stripe::setApiKey( $stripe_secret_key );
					  $Charge = \Stripe\Charge::create([
		                "amount" => $attributes['amount'] * 100,
		                "currency" => $attributes['currency'],
		                "customer" => $attributes['customer'],
		                "card" => $attributes['card'],
		                "description" => $attributes['description'],
		                "receipt_email" => $attributes['receipt_email']
		             ]);
					$log->response = json_encode($Charge);
                	$log->save();

					$paymentId = $Charge['id'];

					return (Object)['status' => 'SUCCESS', 'payment_id' => $paymentId];

				} catch(StripeInvalidRequestError $e){
					// echo $e->getMessage();exit;
					return (Object)['status' => 'FAILURE', 'message' => $e->getMessage()];

	            } catch(Exception $e) {
	                return (Object)['status' => 'FAILURE','message' => $e->getMessage()];
	            }

				break;

			default:
				return redirect('dashboard');
		}
		

	}
	
}