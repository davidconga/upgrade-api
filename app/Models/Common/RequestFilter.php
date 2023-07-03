<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use Auth;

class RequestFilter extends BaseModel
{
	protected $connection = 'common';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id','provider_id','status','service_id','is_cancelled'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'company_id', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * The services that belong to the user.
     */
    public function request()
    {
        return $this->belongsTo('App\Models\Common\UserRequest', 'request_id','id')->where(function ($query) {
            $query->whereNull('provider_id')
                  ->orWhere('provider_id',Auth::guard('provider')->user()->id);
        });
    }

    public function accepted_request()
    {
        return $this->belongsTo('App\Models\Common\UserRequest', 'request_id','id')->where('status', '!=', 'SEARCHING');

    }
    /**
     * The services that belong to the user.
     */
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceCategory','service_id');
    }
}
