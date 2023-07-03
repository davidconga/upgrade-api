<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class ReferralHistory extends BaseModel
{
	protected $connection = 'common';

    protected $fillable = [
        'company_id',
        'referrer_id',        
        'type',        
        'referral_id',
        'referral_data', 
        'status',       
    ];
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }
 

    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }
}
