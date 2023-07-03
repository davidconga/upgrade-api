<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Notifications extends BaseModel
{
    protected $connection = 'common';

    protected $appends = ['expiry_time'];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('notify_type', 'like', "%" . $searchText . "%")
            ->orWhere('descriptions', 'like', "%" . $searchText . "%")
            ->orWhere('expiry_date', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%");
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notify_type',
        'company_id',
        'service',
        'image',
        'description',
        'expiry_date',
        'status'        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public function getExpiryTimeAttribute() {
        return (isset($this->attributes['expiry_date'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['expiry_date'], 'UTC'))->format('m-d-Y') : '' ;
        
    }
  
}
