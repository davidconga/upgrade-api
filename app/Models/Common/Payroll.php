<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
     protected $connection = 'common';
	
	protected $fillable = [
        'template_id','company_id','transaction_id','status','provider_id','wallet','zone_id','payroll_type','type','admin_service','created_at', 'updated_at'
    ];
    /*protected $hidden = [
     	'created_at', 'updated_at'
     ];*/

    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id', 'id');
    }

    public function scopeSearch($query, $searchText='') {
        return $query->where('transaction_id', 'like', "%" . $searchText . "%")
            ->orWhere('payroll_type', 'like', "%" . $searchText . "%") ;
            
    }

    public function bankDetails()
    {
        return $this->hasmany('App\Models\Common\ProviderBankdetail','type_id','provider_id')->where('created_type','PROVIDER');
    }
}
