<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class ProviderVehicle extends BaseModel
{
    protected $connection = 'common';

    protected $fillable = [
        'vehicle_service_id',
        'vehicle_model',
        'vehicle_no',
    ];
    public function vehicle_type()
    {
        return $this->belongsTo('App\Models\Transport\RideDeliveryVehicle', 'vehicle_service_id', 'id');
    }

    protected $hidden = [
        'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function payment()
    {
        return $this->hasOne('App\Models\Transport\RideDeliveryVehicle', 'ride_request_id');
    }
    public function provider_service()
    {
        return $this->belongsTo('App\Models\Common\ProviderService', 'id', 'provider_vehicle_id')->with('admin_service');
    }
}
