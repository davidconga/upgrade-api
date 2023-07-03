<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentGateway;
use App\Models\Common\PaymentLog;
use App\Services\SendPushNotification;
use App\Models\Common\Country;
use App\Models\Common\Setting;
use App\Models\Common\State;
use App\Models\Common\City;
use App\Models\Common\Menu;
use App\Models\Common\Card;
use App\Models\Common\User;
use App\Models\Common\Provider;
use App\Helpers\Helper;
use App\Models\Common\Settings;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderCard;
use App\Models\Common\ProviderWallet;
use App\Services\Transactions;
use Razorpay\Api\Api;
use Auth;

class PaymentController extends Controller
{
	/**
     * add wallet money for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_money(Request $request)
    {
        $this->validate($request, [
            'user_type' => 'required',
            'amount' => 'required',
            'payment_mode' => 'required'
        ]);

        $settings = json_decode(json_encode(Setting::first()->settings_data));
        $paymentConfig = json_decode( json_encode( $settings->payment ) , true);

        $cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'RAZORPAY'; }));
        $card = 0;

        $stripe_secret_key = "";
        $stripe_publishable_key = "";
        $stripe_currency = "";

        if(count($cardObject) > 0) { 
            $card = $cardObject[0]['status'];

            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_secret_key'; }));
            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_api_key'; }));
            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'razorpay_currency'; }));

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

        $random = 'TRNX'.mt_rand(100000, 999999);

        $user_type = $request->user_type;
 
        $log = new PaymentLog();
        $log->user_type = $user_type;
        $log->admin_service = 'WALLET';
        $log->is_wallet = '1';
        $log->amount = $request->amount;
        $log->transaction_code = $random;
        $log->payment_mode = strtoupper($request->payment_mode);
        $log->user_id = Auth::guard($user_type)->user()->id;
        $log->company_id = Auth::guard($user_type)->user()->company_id;
        $log->save();

        switch (strtoupper($request->payment_mode)) {

          case 'BRAINTREE':

           $gateway = new PaymentGateway('braintree');
            return $gateway->process([
                'amount' => $request->amount,
                'nonce' => $request->braintree_nonce,
                'order' => $random,
            ]);

            break;

          case 'CARD':
            
            if ($user_type == 'provider') {

                ProviderCard::where('provider_id', Auth::guard('provider')->user()->id)->update(['is_default' => 0]);
                ProviderCard::where('card_id', $request->card_id)->update(['is_default' => 1]);


            } else {
                Card::where('user_id', Auth::guard('user')->user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }

            
            $gateway = new PaymentGateway('stripe');
            $response = $gateway->process([
                "order" => $random,
                "amount" => $request->amount,
                "currency" => 'USD',
                "customer" => Auth::guard($user_type)->user()->stripe_cust_id, 
                "card" => $request->card_id,
                "description" => "Adding Money for " . Auth::guard($user_type)->user()->email,
                "receipt_email" => Auth::guard($user_type)->user()->email
            ]);
            if($response->status == "SUCCESS") { 
                if($user_type == 'user'){

                    //create transaction to user wallet
                    $transaction['id']=Auth::guard('user')->user()->id;
                    $transaction['amount']=$log->amount;
                    $transaction['company_id']=$log->company_id;                                        
                    (new Transactions)->userCreditDebit($transaction,1);

                    //update wallet balance
                    $wallet_balance = Auth::guard('user')->user()->wallet_balance+$log->amount;
                    User::where('id',Auth::guard('user')->user()->id)
                    ->where('company_id',Auth::guard('user')->user()->company_id)->update(['wallet_balance' => $wallet_balance]);

                    (new SendPushNotification)->WalletMoney(Auth::guard('user')->user()->id, Auth::guard('user')->user()->currency_symbol.$log->amount, 'common', 'Wallet amount added', ['amount' => $log->amount]);
                }else{
       
                    //create transaction to provider wallet
                    $transaction['id']=Auth::guard('provider')->user()->id;
                    $transaction['amount']=$log->amount;
                    $transaction['company_id']=$log->company_id;                                        
                    (new Transactions)->providerCreditDebit($transaction,1);

                    //update wallet balance
                    $wallet_balance = Auth::guard('provider')->user()->wallet_balance+$log->amount;

                    Provider::where('id',Auth::guard('provider')->user()->id)
                    ->where('company_id',Auth::guard('provider')->user()->company_id)->update(['wallet_balance' => $wallet_balance]);

                    (new SendPushNotification)->ProviderWalletMoney(Auth::guard('provider')->user()->id, Auth::guard('provider')->user()->currency_symbol.$log->amount, 'common', 'Wallet amount added', ['amount' => $log->amount]);
                }

                return Helper::getResponse(['data'=> ['wallet_balance' => $wallet_balance],'message' => trans('api.amount_added_to_your_wallet')]);
            }else{
                return Helper::getResponse(['status' => '500', 'message' => trans('Transaction Failed')]);
            }
            break;
            case 'RAZORPAY':
           

             $api = new Api('rzp_test_AbxUJ2sx4IaEwO', '81SQWWfRy2gAJ2MsWb8zJ9wS');
            //Fetch payment information by razorpay_payment_id
            $payment = $api->payment->fetch($request->payment_id);
          
            if( !empty($request->payment_id)) {
            try {

                $response = $api->payment->fetch($request->payment_id)->capture(array('amount'=>$request->razorpay_amount));
                if($response->status == "captured"){

                    if($user_type == 'user'){

                    //create transaction to user wallet
                    $transaction['id']=Auth::guard('user')->user()->id;
                    $transaction['amount']=$request->amount;
                    $transaction['company_id']=$log->company_id;                                        
                    (new Transactions)->userCreditDebit($transaction,1);

                    //update wallet balance
                    $wallet_balance = Auth::guard('user')->user()->wallet_balance+$request->amount;
                    User::where('id',Auth::guard('user')->user()->id)
                    ->where('company_id',Auth::guard('user')->user()->company_id)->update(['wallet_balance' => $wallet_balance]);

                    (new SendPushNotification)->WalletMoney(Auth::guard('user')->user()->id, Auth::guard('user')->user()->currency_symbol.$request->amount, 'common', 'Wallet amount added', ['amount' => $request->amount]);
                }else{
       
                    //create transaction to provider wallet
                    $transaction['id']=Auth::guard('provider')->user()->id;
                    $transaction['amount']=$request->amount;
                    $transaction['company_id']=$log->company_id;                                        
                    (new Transactions)->providerCreditDebit($transaction,1);

                    //update wallet balance
                    $wallet_balance = Auth::guard('provider')->user()->wallet_balance+$request->amount;

                    Provider::where('id',Auth::guard('provider')->user()->id)
                    ->where('company_id',Auth::guard('provider')->user()->company_id)->update(['wallet_balance' => $wallet_balance]);

                    (new SendPushNotification)->ProviderWalletMoney(Auth::guard('provider')->user()->id, Auth::guard('provider')->user()->currency_symbol.$request->amount, 'common', 'Wallet amount added', ['amount' => $request->amount]);
                }

                } 
                return Helper::getResponse(['data'=> ['wallet_balance' => $wallet_balance],'message' => trans('api.amount_added_to_your_wallet')]);

            } catch (\Exception $e) {
               return Helper::getResponse(['status' => '500', 'message' => $e->getMessage()]);
            }

            // Do something here for store payment details in database...
        }
             
            break;

        }
    }
}
