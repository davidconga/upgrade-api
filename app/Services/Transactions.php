<?php 

namespace App\Services;

use Illuminate\Http\Request;
use Validator;
use Exception;
use DateTime;
use Carbon\Carbon;
use Auth;
use Lang;
use App\Helpers\Helper;
use GuzzleHttp\Client;
use App\Models\Transport\RideCityPrice;
use App\Models\Common\PeakHour;
use App\Models\Common\AdminWallet;
use App\Models\Common\User;
use App\Models\Common\UserWallet;
use App\Models\Common\FleetWallet;
use App\Models\Common\Provider;
use App\Models\Common\ProviderWallet;
use App\Models\Common\Admin;


class Transactions{

    public function __construct(){}

    protected function createAdminWallet($request){

        $admin_data=AdminWallet::orderBy('id', 'DESC')->first();

        $adminwallet=new AdminWallet;
        $adminwallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $adminwallet->admin_service=$request['admin_service'];
        if(!empty($request['country_id']))
            $adminwallet->country_id=$request['country_id'];
        $adminwallet->transaction_id=$request['transaction_id'];        
        $adminwallet->transaction_alias=$request['transaction_alias'];
        $adminwallet->transaction_desc=$request['transaction_desc'];
        $adminwallet->transaction_type=$request['transaction_type'];
        $adminwallet->type=$request['type'];
        $adminwallet->amount=$request['amount'];

        if(empty($admin_data->close_balance))
            $adminwallet->open_balance=0;
        else
            $adminwallet->open_balance=$admin_data->close_balance;

        if(empty($admin_data->close_balance))
            $adminwallet->close_balance=$request['amount'];
        else            
            $adminwallet->close_balance=$admin_data->close_balance+($request['amount']);        

        $adminwallet->save();

        return $adminwallet;
    }

    protected function createUserWallet($request){
        
        $user=User::findOrFail($request['id']);

        $userWallet=new UserWallet;
        $userWallet->user_id=$request['id'];
        $userWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $userWallet->admin_service=$request['admin_service']; 
        $userWallet->transaction_id=$request['transaction_id'];        
        $userWallet->transaction_alias=$request['transaction_alias'];
        $userWallet->transaction_desc=$request['transaction_desc'];
        $userWallet->type=$request['type'];
        $userWallet->amount=$request['amount'];        

        if(empty($user->wallet_balance))
            $userWallet->open_balance=0;
        else
            $userWallet->open_balance=$user->wallet_balance;

        if(empty($user->wallet_balance))
            $userWallet->close_balance=$request['amount'];
        else            
            $userWallet->close_balance=$user->wallet_balance+($request['amount']);

        $userWallet->save();

        //update the user wallet amount to user table        
        $user->wallet_balance=$user->wallet_balance+($request['amount']);
        $user->save();

        return $userWallet;
    }

    protected function createProviderWallet($request){
        
        $provider=Provider::findOrFail($request['id']);

        $providerWallet=new ProviderWallet;        
        $providerWallet->provider_id=$request['id'];
        $providerWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $providerWallet->admin_service=$request['admin_service'];        
        $providerWallet->transaction_id=$request['transaction_id'];        
        $providerWallet->transaction_alias=$request['transaction_alias'];
        $providerWallet->transaction_desc=$request['transaction_desc'];
        $providerWallet->type=$request['type'];
        $providerWallet->amount=$request['amount'];

        if(empty($provider->wallet_balance))
            $providerWallet->open_balance=0;
        else
            $providerWallet->open_balance=$provider->wallet_balance;

        if(empty($provider->wallet_balance))
            $providerWallet->close_balance=$request['amount'];
        else            
            $providerWallet->close_balance=$provider->wallet_balance+($request['amount']);        

        $providerWallet->save();

        //update the provider wallet amount to provider table        
        $provider->wallet_balance=$provider->wallet_balance+($request['amount']);
        $provider->save();

        return $providerWallet;

    }

    protected function createFleetWallet($request){

        $fleet=Admin::findOrFail($request['id']);

        $fleetWallet=new FleetWallet;
        $fleetWallet->fleet_id=$request['id'];
        $fleetWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $fleetWallet->admin_service=$request['admin_service'];
        $fleetWallet->transaction_id=$request['transaction_id'];        
        $fleetWallet->transaction_alias=$request['transaction_alias'];
        $fleetWallet->transaction_desc=$request['transaction_desc'];
        $fleetWallet->type=$request['type'];
        $fleetWallet->amount=$request['amount'];        

        if(empty($fleet->wallet_balance))
            $fleetWallet->open_balance=0;
        else
            $fleetWallet->open_balance=$fleet->wallet_balance;

        if(empty($fleet->wallet_balance))
            $fleetWallet->close_balance=$request['amount'];
        else            
            $fleetWallet->close_balance=$fleet->wallet_balance+($request['amount']);       

        $fleetWallet->save();

        //update the fleet wallet amount to fleet table        
        $fleet->wallet_balance=$fleet->wallet_balance+($request['amount']);
        $fleet->save();

        return true;
    }

    public function adminCommission($request){
            
        $request['transaction_desc']=trans('api.transaction.admin_commission');
        $request['transaction_type']=1;
        $request['type']='C';        
        $this->createAdminWallet($request);

        $provider_det_amt= -1 * abs($request['amount']);
        $request['transaction_desc']=trans('api.transaction.admin_commission');
        $request['id']=$request['id'];
        $request['type']='D';
        $request['amount']=$provider_det_amt;
        $this->createProviderWallet($request);
    }

    public function fleetCommission($request){

        $amount=$request['amount'];
        $admin_det_amt= -1 * abs($request['amount']);
        $request['transaction_desc']=trans('api.transaction.fleet_debit');
        $request['transaction_type']=7;
        $request['type']='D';
        $request['amount']=$admin_det_amt;
        $this->createAdminWallet($request);
                 
        $request['transaction_desc']=trans('api.transaction.fleet_add');
        $request['id']=$request['id'];        
        $request['type']='C';
        $request['amount']=$amount;
        $this->createFleetWallet($request);
                              
        $request['transaction_desc']=trans('api.transaction.fleet_recharge');
        $request['transaction_type']=6;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);       

        return true;
    }

    public function providerDiscountCredit($request){
        
        $amount=$request['amount'];
        $ad_det_amt= -1 * abs($request['amount']);                            
        $request['transaction_desc']=trans('api.transaction.discount_apply');
        $request['transaction_type']=10;       
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createAdminWallet($request);
                    
        $request['transaction_desc']=trans('api.transaction.discount_refund');
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$amount;
        $this->createProviderWallet($request);

        $request['transaction_desc']=trans('api.transaction.discount_recharge');
        $request['transaction_type']=11;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);

        return true;
    }

    public function taxCredit($request){        
        
        $amount=$request['amount'];
        $ad_det_amt= -1 * abs($request['amount']);                              
        $request['transaction_desc']=trans('api.transaction.tax_debit');
        $request['id']=$request['id'];
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createProviderWallet($request);       
                          
        $request['transaction_desc']=trans('api.transaction.tax_credit');
        $request['transaction_type']=9;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);

        return true;
    }

    public function waitingAmount($request){

        $amount=$request['amount'];
        $ad_det_amt= -1 * abs($request['amount']);
        $request['transaction_desc']=trans('api.transaction.waiting_commission');
        $request['id']=$request['id'];
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createProviderWallet($request);
                               
        $request['transaction_desc']=trans('api.transaction.waiting_commission');
        $request['transaction_type']=15;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);

        return true;
    }

    public function peakAmount($request){        

        $amount=$request['amount'];
        $ad_det_amt= -1 * abs($request['amount']);
        $request['transaction_desc']=trans('api.transaction.peak_commission');
        $request['id']=$request['id'];
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createProviderWallet($request);
                               
        $request['transaction_desc']=trans('api.transaction.peak_commission');
        $request['transaction_type']=14;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);

        return true;
    }    

    public function providerRideCredit($request){

        $request['transaction_desc']=trans('api.transaction.provider_credit');        
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$request['amount'];
        $admin_amount=$request['admin_amount'];
        $this->createProviderWallet($request);

        if($admin_amount>0){                              
            $request['transaction_desc']=trans('api.transaction.provider_recharge');
            $request['transaction_type']=4;                     
            $request['type']='C';
            $request['amount']=$request['admin_amount'];
            $this->createAdminWallet($request);
        }    

        return true;
    }

    public function transationAlias($userType, $paymentType = null) {
        if($userType == 'user') {
            $user_data=UserWallet::orderBy('id', 'DESC')->first();
            $prefix = ($paymentType != null) ? 'RFU' : 'URC';
        } else {
            $user_data=ProviderWallet::orderBy('id', 'DESC')->first();
            $prefix = ($paymentType != null) ? 'RFP' : 'PRC';
        }
        
        if(!empty($user_data))
            $transaction_id=$user_data->id+1;
        else
           $transaction_id=1;

        $respone['transaction_alias'] = $prefix.str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
        $respone['transaction_id']= $transaction_id;

        return $respone;
    }

    public function userCreditDebit($request,$type=1){
        // dd(55);
        if($type==1){
            $msg=trans('api.transaction.user_recharge');           
            $ttype='C';
            $amount= $request['amount'];
            $get_response=$this->transationAlias('user');
            $transaction_id=$get_response['transaction_id'];
            $transaction_alias= $get_response['transaction_alias'];
            $user_id=$request['id'];
            $transaction_type=2;
        }
        else{
            $msg=$request['transaction_msg'];            
            $ttype='D';
            $amount= -1 * abs($request['amount']);
            $transaction_id=$request['transaction_id'];
            $transaction_alias=$request['transaction_alias'];
            $user_id=$request['id'];
            $transaction_type=3;
        }
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['transaction_type']=$transaction_type;        
        $ipdata['type']=$ttype;
        $ipdata['amount']=$amount;
        $this->createAdminWallet($ipdata);           
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['id']=$user_id;        
        $ipdata['type']=$ttype;
        $ipdata['amount']=$amount;
        return $this->createUserWallet($ipdata); 
         
    }

    public function providerCreditDebit($request,$type=1){

        if($type==1){
            $amount= $request['amount'];
            $msg=trans('api.transaction.user_recharge');           
            $ttype='C';
            $get_response=$this->transationAlias('provider');
            $transaction_id=$get_response['transaction_id'];
            $transaction_alias= $get_response['transaction_alias'];
            $user_id=$request['id'];
            $transaction_type=2;
        }
        else{
            $msg=trans('api.transaction.user_trip');            
            $ttype='D';
            $amount= -1 * abs($request['amount']);
            $transaction_id=$request['transaction_id'];
            $transaction_alias=$request['transaction_alias'];
            $user_id=$request['id'];
            $transaction_type=3;
        }
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['transaction_type']=$transaction_type;        
        $ipdata['type']=$ttype;
        $ipdata['amount']=$amount;
        $this->createAdminWallet($ipdata);           
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['id']=$user_id;        
        $ipdata['type']=$ttype;
        $ipdata['amount']=$amount;
        return $this->createProviderWallet($ipdata); 
         
    }

    public function referralCreditDebit($request,$type=1){

        if($type==1){
            $msg=trans('api.transaction.referal_recharge');           
            $ttype='C';                        
            $get_response=$this->transationAlias('user', 'refer');
            $transaction_id=$get_response['transaction_id'];
            $transaction_alias= $get_response['transaction_alias'];
            $transaction_type=12;

            $ipdata=array();
            $ipdata['company_id']=$request['company_id'];
            $ipdata['transaction_id']=$transaction_id;
            $ipdata['transaction_alias']=$transaction_alias;
            $ipdata['transaction_desc']=$msg;
            $ipdata['id']=$request['id'];        
            $ipdata['type']=$ttype;
            $ipdata['amount']=$request['amount'];
            $this->createUserWallet($ipdata);
        }
        else{
            $msg=trans('api.transaction.referal_recharge');           
            $ttype='C';
            $get_response=$this->transationAlias('provider', 'refer');
            $transaction_id=$get_response['transaction_id'];
            $transaction_alias= $get_response['transaction_alias'];
            $transaction_type=13;

            $ipdata=array();
            $ipdata['company_id']=$request['company_id'];
            $ipdata['transaction_id']=$transaction_id;
            $ipdata['transaction_alias']=$transaction_alias;
            $ipdata['transaction_desc']=$msg;
            $ipdata['id']=$request['id'];        
            $ipdata['type']=$ttype;
            $ipdata['amount']=$request['amount'];
            $this->createProviderWallet($ipdata);
        }
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['transaction_type']=$transaction_type;        
        $ipdata['type']='D';
        $ipdata['amount']=-1 * abs($request['amount']);
        $this->createAdminWallet($ipdata);          

        return true;
    }
    public function AdminAddAmountCreditDebit($request,$type=1){
        $msg=$request['message'];           
        $ttype='C';
        $user_data=ProviderWallet::orderBy('id', 'DESC')->first();
        if(!empty($user_data))
            $transaction_id=$user_data->id+1;
        else
            $transaction_id=1;

        $transaction_alias= 'AP'.str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
        
        $transaction_type=17;

        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['id']=$request['id'];        
        $ipdata['type']=$ttype;
        $ipdata['amount']=$request['amount'];
        $this->createProviderWallet($ipdata);
        
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['transaction_type']=$transaction_type;
        $ipdata['id']=$request['id'];        
        $ipdata['type']='D';
        $ipdata['amount']=-1 * abs($request['amount']);
        $this->createAdminWallet($ipdata);          

        return true;
    }

    public function disputeCreditDebit($request,$type=1){

        if($type==1){
            $msg=$request['message'];           
            $ttype='C';
            $user_data=UserWallet::orderBy('id', 'DESC')->first();
            if(!empty($user_data))
                $transaction_id=$user_data->id+1;
            else
                $transaction_id=1;

            $transaction_alias= 'DPU'.str_pad($transaction_id, 6, 0, STR_PAD_LEFT);

            $transaction_type=16;

            $ipdata=array();
            $ipdata['company_id']=$request['company_id'];
            $ipdata['transaction_id']=$transaction_id;
            $ipdata['transaction_alias']=$transaction_alias;
            $ipdata['transaction_desc']=$msg;
            $ipdata['id']=$request['id'];        
            $ipdata['type']=$ttype;
            $ipdata['amount']=$request['amount'];
            $this->createUserWallet($ipdata);
        }
        else{
            $msg=$request['message'];           
            $ttype='C';
            $user_data=ProviderWallet::orderBy('id', 'DESC')->first();
            if(!empty($user_data))
                $transaction_id=$user_data->id+1;
            else
                $transaction_id=1;

            $transaction_alias= 'DPP'.str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
                        
            $transaction_type=17;

            $ipdata=array();
            $ipdata['company_id']=$request['company_id'];
            $ipdata['transaction_id']=$transaction_id;
            $ipdata['transaction_alias']=$transaction_alias;
            $ipdata['transaction_desc']=$msg;
            $ipdata['id']=$request['id'];        
            $ipdata['type']=$ttype;
            $ipdata['amount']=$request['amount'];
            $this->createProviderWallet($ipdata);
        }
        
        $ipdata=array();
        $ipdata['company_id']=$request['company_id'];
        $ipdata['transaction_id']=$transaction_id;
        $ipdata['transaction_alias']=$transaction_alias;
        $ipdata['transaction_desc']=$msg;
        $ipdata['transaction_type']=$transaction_type;
        $ipdata['id']=$request['id'];        
        $ipdata['type']='D';
        $ipdata['amount']=-1 * abs($request['amount']);
        $this->createAdminWallet($ipdata);          

        return true;
    }
}
