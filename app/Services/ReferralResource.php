<?php 

namespace App\Services;

use Illuminate\Http\Request;
use App\UserRequests;
use App\Helpers\Helper;
use Auth;
use App\Models\Common\User;
use App\Models\Common\Setting;
use App\Models\Common\Provider;
use App\Models\Common\ReferralHistory;
use App\Models\Common\ReferralEarning;
use App\Services\Transactions;
use DB;
use Validator;
use Illuminate\Validation\Rule;

class ReferralResource {

    public function __construct(){}

    /**
        * Get a validator for a tradepost.
        *
        * @param  array $data
        * @return \Illuminate\Contracts\Validation\Validator
    */
    public function checkReferralCode(array $data)
    {
             
        $rules = [
            'referral_unique_id'  => [ Rule::unique('users')->where(function ($query) use($data) {
                            return $query->where('company_id', $data['company_id']);
                         }),
                       Rule::unique('providers')->where(function ($query) use($data) {
                            return $query->where('company_id', $data['company_id']);
                         })]
        ];
        $messages = [
            'referral_unique_id.unique'  => 'referral_code_already exits',
        ];

         return Validator::make($data,$rules,$messages);
         
    }

    public function generateCode($company_id,$length = 6) {

        $az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $azr = rand(0, 51);
        $azs = substr($az, $azr, 10);
        $stamp = hash('sha256', time());
        $mt = hash('sha256', mt_rand(5, 20));
        $alpha = hash('sha256', $azs);
        $hash = str_shuffle($stamp . $mt . $alpha);
        $code = strtoupper(substr($hash, $azr, $length));        
        $data['referral_unique_id']=$code;
        $data['company_id'] = $company_id;


        $validator  = $this->checkReferralCode($data);

        if ($validator->fails()) {
                    
            $this->generateCode($company_id);
        }    
        
        return $code;

    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function create_referral($referral_code, $referral_data, $settings, $referrer)
    {
        
        if($referrer == "user") {
            $type=1;           
            $referral=User::where('referral_unique_id',$referral_code)->where('company_id', $referral_data->company_id)->first();
        } else if($referrer == "provider") {
            $type=2;
            $referral=Provider::where('referral_unique_id',$referral_code)->where('company_id', $referral_data->company_id)->first();
        }

        if(!empty($referral)){

            $siteConfig = $settings->site;

            //insert referral histroy
            $User = ReferralHistory::create([
                'company_id' => $referral_data->company_id,
                'referrer_id' => $referral->id,
                'type' => $type,
                'referral_id' => $referral_data->id,
                'referral_data' => json_encode($referral_data),
                'status' => 'P'            
            ]);
            if($User){
                $referral->referal_count = $referral->referal_count+1;
                $referral->save();
            }

            if($siteConfig->referral_amount >0){
            
                $ReferralHistory=ReferralHistory::select(DB::raw("group_concat(id) as ids"))->where('referrer_id',$referral->id)->where('type',$type)->where('status','P')->where('company_id',$referral->company_id)->groupBy('referrer_id')->get();

                $Referralcount=0;$ids=NULL;

                if($ReferralHistory->count()>0){
                    $Referralcount=count(explode(',',$ReferralHistory[0]->ids));
                    $ids=$ReferralHistory[0]->ids;
                }

                Provider::find($referral->id)->update(['referal_count'=>$Referralcount]);

                if(($siteConfig->referral_count >0) && ($siteConfig->referral_count == $Referralcount)){
                     
                    //create referral earnings
                    $Earnings = ReferralEarning::create([
                        'company_id' => $referral_data->company_id,
                        'referrer_id' => $referral->id,
                        'type' => $type,
                        'amount' => $siteConfig->referral_amount,
                        'count' => $Referralcount,
                        'referral_histroy_id' => $ids            
                    ]);

                    //create amount to user/provider wallet
                    $transaction['id']=$referral->id;
                    $transaction['company_id']=$referral->company_id;
                    $transaction['amount']=$siteConfig->referral_amount;
                    (new Transactions)->referralCreditDebit($transaction,$type);

                    //update histroy table process status to complete
                    ReferralHistory::where('referrer_id',$referral->id)->where('type',$type)->where('status','P')->where('company_id', $referral->company_id)->update(['status' => 'C']);
                                  
                }
            }
        }            
    }

    public function get_referral($type,$user_id)
    {
        $ReferralHistory=ReferralEarning::where('referrer_id',$user_id)->where('type',$type)->get( array(
            DB::raw( 'COALESCE(SUM(count), 0) AS total_count' ),
            DB::raw( 'COALESCE(SUM(amount), 0) AS total_amount' ),
        ));
        return $ReferralHistory;                   
    }
}