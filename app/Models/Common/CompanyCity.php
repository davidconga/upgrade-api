<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class CompanyCity extends BaseModel
{
    protected $connection = 'common';
    
    protected $hidden = [
        'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function country()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }
    public function state()
    {
        return $this->belongsTo('App\Models\Common\State', 'state_id', 'id');
    }
    public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }
    public function city_list()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id'); 
    }
    public function city_service()
    {
        return $this->hasMany('App\Models\Common\CompanyCityAdminService', 'company_city_service_id', 'id')->with('admin_service');
    }

    public function menu_city()
    {
        return $this->hasone('App\Models\Common\MenuCity', 'city_id', 'city_id');
    }

  


    public function scopeSearch($query, $searchText='') {
        return $query
                        ->whereHas('country', function ($q) use ($searchText){
                            $q->where('country_name', 'like', "%" . $searchText . "%");
                        })
                        ->orwhereHas('state', function ($q) use ($searchText){
                            $q->where('state_name', 'like', "%" . $searchText . "%");
                        })
                        ->orwhereHas('city', function ($q) use ($searchText){
                            $q->where('city_name', 'like', "%" . $searchText . "%");
                        })
                        ->orWhere('status', 'like', "%" . $searchText . "%");
    }
}
