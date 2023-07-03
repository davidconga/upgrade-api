<?php

namespace App\Models\Common;
use App\Models\BaseModel;
use Auth;


class CountryBankForm extends BaseModel
{
    protected $connection = 'common';
	
	protected $hidden = [
     	'created_type','created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function country()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }

       public function bankdetails()
    {
     
    return $this->hasone('App\Models\Common\ProviderBankdetail', 'bankform_id','id');
    }
    public function getLabelAttribute()
    {
        if(Auth::guard('admin')->check()){
           $provider = Admin::where('id',\Auth::guard('admin')->user()->id)->first();
        }elseif(Auth::guard('user')->check()){
           $provider = User::where('id',\Auth::guard('user')->user()->id)->first();
        }elseif(Auth::guard('provider')->check()){
           $provider = Provider::where('id',\Auth::guard('provider')->user()->id)->first();
        }
        \Log::info($provider->language);
         if($provider->language){
            $language = $provider->language;
         }
         else{
            $language = "en";
         }
         $column = "label_".$language;
         return "{$this->$column}";
    }
}
