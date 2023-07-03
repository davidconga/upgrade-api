<?php

namespace App\Models\Service;

use App\Models\BaseModel;
use Auth;

class ServiceSubcategory extends BaseModel
{
    protected $connection = 'service';

    public function scopeSearch($query, $searchText='') {
        $word = 'active';
        $word2 = 'inactive';
        if (strpos($word, $searchText) !== FALSE) {
            $result =  $query
            ->where('service_subcategory_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_status', 1);
        }if (strpos($word2, $searchText) !== FALSE) {            
            $result =  $query
            ->where('service_subcategory_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_status', 2);
        }else{
            $result =  $query
            ->where('service_subcategory_name', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_order', 'like', "%" . $searchText . "%")
            ->orWhere('service_subcategory_status', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceCategory');
    }

    public function service()
    {
        return $this->hasMany('App\Models\Service\Service', 'service_subcategory_id', 'id');
    }

     public function providerservicesubcategory() {
    return $this->hasMany('App\Models\Common\ProviderService','sub_category_id','id')->where('admin_service','SERVICE')->where('provider_id',Auth::guard('provider')->user()->id);
    }

}
