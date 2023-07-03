<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class CompanyCountry extends BaseModel
{
	protected $connection = 'common';
	
	protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function country()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }
    public function companyCountryCities()
    {
        return $this->hasMany('App\Models\Common\CompanyCity', 'country_id', 'country_id')->with('city');
    }

    public function scopeSearch($query, $searchText='') {


        return $query
                        ->whereHas('country', function ($q) use ($searchText){
                            $q->where('country_name', 'like', "%" . $searchText . "%");
                        })
                        ->orwhere('currency', 'like', "%" . $searchText . "%")
                        ->orwhere('currency_code', 'like', "%" . $searchText . "%")
                        ->orWhere('status', 'like', "%" . $searchText . "%");


    }
}