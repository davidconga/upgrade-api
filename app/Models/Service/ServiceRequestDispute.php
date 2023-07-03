<?php

namespace App\Models\Service;

use App\Models\BaseModel;

class ServiceRequestDispute extends BaseModel
{
    protected $connection = 'service';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by','updated_at', 'deleted_at'
     ];
    
    public function scopeSearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('dispute_type', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orWhere('comments', 'like', "%" . $searchText . "%")
            ->orWhere('dispute_name', 'like', "%" . $searchText . "%")
            ->orwhereHas('request', function ($q) use ($searchText){
                            $q->where('booking_id', 'like', "%" . $searchText . "%");
                        });
        }
        return $result;
    }
      /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function request()
    {
        return $this->belongsTo('App\Models\Service\ServiceRequest','service_request_id');
    }
}
