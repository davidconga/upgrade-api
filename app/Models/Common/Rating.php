<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Rating extends BaseModel
{
	protected $connection = 'common';
	
    protected $fillable = [
        'admin_service', 'request_id', 'user_id', 'provider_id', 'company_id', 'user_rating', 'provider_rating', 'store_rating', 'user_comment', 'provider_comment', 'store_comment'
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }


    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function scopeUsersearch($query, $searchText='') {
        return $query
            ->where('admin_service', 'like', "%" . $searchText . "%")
            ->orwhereHas('user', function ($q) use ($searchText){
                $q->where('first_name', 'like', "%" . $searchText . "%");
                $q->orwhere('last_name', 'like', "%" . $searchText . "%");
            })
            ->orwhereHas('provider', function ($q) use ($searchText){
                $q->where('first_name', 'like', "%" . $searchText . "%");
                 $q->orwhere('last_name', 'like', "%" . $searchText . "%");
            })
            ->orwhere('created_at','like',"%".$searchText."%")
            ->orWhere('user_rating', 'like', "%" . $searchText . "%");
    }

    public function scopeProvidersearch($query, $searchText='') {
        return $query
            ->where('admin_service', 'like', "%" . $searchText . "%")
            ->orwhereHas('user', function ($q) use ($searchText){
                $q->where('first_name', 'like', "%" . $searchText . "%");
                $q->orwhere('last_name', 'like', "%" . $searchText . "%");
            })
            ->orwhereHas('provider', function ($q) use ($searchText){
                $q->where('first_name', 'like', "%" . $searchText . "%");
                $q->orwhere('last_name', 'like', "%" . $searchText . "%");
            })
            ->orwhere('created_at','like',"%".$searchText."%")
            ->orWhere('provider_rating', 'like', "%" . $searchText . "%");
    }
}
