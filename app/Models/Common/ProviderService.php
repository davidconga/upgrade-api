<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class ProviderService extends BaseModel
{
	protected $connection = 'common';

    protected $casts = [
        'base_fare' => 'float',
        'per_miles' => 'float',
        'per_mins' => 'float',
    ];

	protected $fillable = [
        'provider_id', 'company_id', 'admin_service', 'provider_vehicle_id', 'ride_delivery_id', 'status','base_fare',
    ];
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];
 
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Common\ProviderVehicle', 'provider_vehicle_id');
    }

    public function ride_vehicle()
    {
        return $this->belongsTo('App\Models\Transport\RideDeliveryVehicle', 'ride_delivery_id');
    }
    
    public function admin_service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service','admin_service');
    }
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id','id')->with('rating');
    }
    

    public function providervehicle()
    {
    return $this->hasone('App\Models\Common\ProviderVehicle', 'id','provider_vehicle_id');
    }
    

    public function maintransport()
    {
        return $this->hasone('App\Models\Transport\RideType', 'id','category_id');
    }

    public function mainservice()
    {
        return $this->hasone('App\Models\Service\ServiceCategory', 'id','category_id');
    }
    public function mainshop()
    {
        return $this->hasone('App\Models\Order\StoreType', 'id','category_id');
    }


}
