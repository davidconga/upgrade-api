<?php

namespace App\Models\Service;

use App\Models\BaseModel;
use Auth;

class ServiceCategory extends BaseModel
{
    protected $connection = 'service';

    public function scopeSearch($query, $searchText='') {
        $word = 'active';
        $word2 = 'inactive';
        if (strpos($word, $searchText) !== FALSE) {
            $result =  $query
            ->where('service_category_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_status', 1);
        }if (strpos($word2, $searchText) !== FALSE) {            
            $result =  $query
            ->where('service_category_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_status', 2);
        }else{
            $result =  $query
            ->where('service_category_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_category_status', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    public function providerservicecategory() {
        return $this->hasMany('App\Models\Common\ProviderService','category_id','id')->where('admin_service','Service')->where('provider_id',Auth::guard('provider')->user()->id);
    }

    
    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];
    
    public function subcategories()
    {
        return $this->hasMany('App\Models\Service\ServiceSubcategory', 'service_category_id', 'id');
    }

    public function services()
    {
        return $this->hasMany('App\Models\Service\Service');
    }
}
