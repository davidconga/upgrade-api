<?php

namespace App\Models\Service;

use App\Models\BaseModel;

class ServiceCityPrice extends BaseModel
{
    protected $connection = 'service';

    protected $casts = [
        'base_fare' => 'float',
        'base_distance' => 'float',
        'per_miles' => 'float',
        'per_mins' => 'float',
        'minimum_fare' => 'float',
        'commission' => 'float',
        'fleet_commission' => 'float',
        'tax' => 'float',
        'cancellation_charge' => 'float'
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function provider_service()
    {
     return $this->hasMany('App\Models\Common\ProviderService', 'service_id', 'id')->with('provider');
    }
    public function country()
    {
    return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }
    public function city()
    {
    return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }
    public function service()
    {
    return $this->belongsTo('App\Models\Service\Service', 'service_id', 'id');
    }
}
