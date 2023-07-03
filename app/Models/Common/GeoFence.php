<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class GeoFence extends BaseModel
{
	protected $connection = 'common';

	protected $table = 'geo_fencings';
	
    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }
}
