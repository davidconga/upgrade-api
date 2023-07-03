<?php

namespace App\Models\Service;

use App\Models\BaseModel;
use Auth;

class Service extends BaseModel
{
    protected $connection = 'service';

    public function scopeSearch($query, $searchText='') {
        $word = 'active';
        $word2 = 'inactive';
        if (strpos($word, $searchText) !== FALSE) {
            $result =  $query
            ->where('service_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_status', 1);
        }if (strpos($word2, $searchText) !== FALSE) {            
            $result =  $query
            ->where('service_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_status', 2);
        }else{
            $result =  $query
            ->where('service_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_status', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceCategory','service_category_id',"id");
    }
    public function servicesubCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceSubcategory','service_subcategory_id','id');
    }
    public function subCategories()
    {
        return $this->hasMany('App\Models\Service\ServiceSubcategory', 'id','service_subcategory_id');
    }

    public function providerservices() {
    return $this->hasMany('App\Models\Common\ProviderService','service_id','id')->where('admin_service','SERVICE')->where('provider_id',Auth::guard('provider')->user()->id);
    }

    public function provideradminservice() {
        return $this->hasOne('App\Models\Common\ProviderService','service_id','id')->where('admin_service','SERVICE');
    }

    public function servicescityprice() {
        return $this->hasone('App\Models\Service\ServiceCityPrice','service_id','id');
    }
    public function service_city() {
        return $this->belongsTo('App\Models\Service\ServiceCityPrice','id','service_id');
    }



}
