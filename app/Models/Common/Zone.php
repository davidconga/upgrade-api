<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $connection = 'common';
	
	protected $fillable = [
        'name','company_id','city_id','user_type','status'
    ];
    protected $hidden = [
     	'created_at', 'updated_at'
     ];

     public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }

    public function scopeSearch($query, $searchText='') {
        return $query->whereHas('city',function($q) use ($searchText){
            $q->where('city_name', 'like', "%" . $searchText . "%");
            })
            ->Orwhere('name', 'like', "%" . $searchText . "%")
            ->Orwhere('user_type', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%") ;
            
    }
}
