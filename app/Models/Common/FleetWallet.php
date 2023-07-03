<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class FleetWallet extends BaseModel
{
	protected $connection = 'common';
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ]; 

       public function admin_service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service','admin_service');
    }

    

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('transaction_alias', 'like', "%" . $searchText . "%")
            ->orWhere('transaction_desc', 'like', "%" . $searchText . "%") 
            ->orWhere('amount', 'like', "%" . $searchText . "%") 
            ->orwhereHas('admin_service', function ($q) use ($searchText){
                    $q->where('admin_service', 'like', "%" . $searchText . "%");
                })
            ->orWhere('type', 'like', "%" . $searchText . "%");
    }
}
