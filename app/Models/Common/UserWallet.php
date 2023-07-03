<?php

namespace App\Models\Common;

use App\Models\BaseModel; 

class UserWallet extends BaseModel
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

      public function user()
     {
         return $this->belongsTo('App\Models\Common\User','user_id','id');
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
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::now()->diffForHumans($this->attributes['created_at'], 'UTC')) : '' ;
        
    }
}
