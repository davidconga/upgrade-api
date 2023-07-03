<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use App\Models\Common\State;
use Auth;

class ProviderWallet extends BaseModel
{
	protected $connection = 'common';

    protected $appends = ['created_time'];
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];
     public function payment_log()
     {
         return $this->belongsTo('App\Models\Common\PaymentLog', 'transaction_id', 'id');
     }

     public function provider()
     {
         return $this->belongsTo('App\Models\Common\Provider', 'provider_id', 'id');
     }

     public function scopeSearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('transaction_alias', 'like', "%" . $searchText . "%")
            ->orWhere('transaction_desc', 'like', "%" . $searchText . "%")
            ->orWhere('amount', 'like', "%" . $searchText . "%")
            ->orWhere('type', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    public function getCreatedTimeAttribute() {
        $timezone=isset(Auth::guard('provider')->user()->state_id) ? State::find(Auth::guard('provider')->user()->state_id)->timezone:"UTC";
        \Log::info($timezone);
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'], 'UTC'))->setTimezone($timezone)->format('Y-m-d H:i:s') : '' ;
        
    }
    public function transactions()
    {
        return $this->hasMany('App\Models\Common\ProviderWallet', 'transaction_alias','transaction_alias');
    }
}
