<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Menu extends BaseModel
{
	protected $connection = 'common';
  // protected $connection = 'transport';

    protected $fillable = [
        'bg_color','icon','title', 'admin_service', 'menu_type_id', 'company_id', 'sort_order'
    ];
	
    protected $hidden = [
     	'company_id', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function service()
    {
       return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }

    public function cities()
    {
       return $this->hasMany('App\Models\Common\MenuCity');
    }
    public function adminservice()
    {
       return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }
    public function ridetype()
    {
       return $this->hasone('App\Models\Transport\RideType', 'menu_type_id','id');
    }

     public function menu_ride()
    {
        return $this->hasone('App\Models\Transport\RideCityPrice', 'id', 'ride_delivery_vehicle_id');
    }

      public function menu_service()
    {
        return $this->hasone('App\Models\Transport\RideCityPrice', 'id', 'ride_delivery_vehicle_id');
    }

    public function scopeSearch($query, $searchText='') {
        return $query
                        ->whereHas('adminservice', function ($q) use ($searchText){
                            $q->where('admin_service', 'like', "%" . $searchText . "%");
                        })
                        ->orWhere('title', 'like', "%" . $searchText . "%");
                        // ->orwhereHas('ridetype', function ($q) use ($searchText){
                        //     $q->where('ride_name', 'like', "%" . $searchText . "%");
                        // })
                        // ->orWhere('bg_color', 'like', "%" . $searchText . "%");
    }
}
