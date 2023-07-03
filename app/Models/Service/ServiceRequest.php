<?php

namespace App\Models\Service;

use App\Models\BaseModel;
use App\Helpers\Helper;

class ServiceRequest extends BaseModel
{
    protected $connection = 'service';

    protected $hidden = [
     	'company_id','created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];

     protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
		'assigned_at',
		'schedule_at',
		'started_at',
		'finished_at',
	];
    protected $appends = ['assigned_time', 'schedule_time', 'started_time', 'finished_time','created_time']; 

    
    public function scopeuserHistorySearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('booking_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orWhere('payment_mode', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    public function scopeProviderhistorySearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('booking_id', 'like', "%" . $searchText . "%")
            ->OrwhereHas('service',function($q) use ($searchText){
            $q->where('service_name', 'like', "%" . $searchText . "%");
            })
            ->OrwhereHas('payment',function($q) use ($searchText){
            $q->where('total', 'like', "%" . $searchText . "%");
            });
            
        }
        return $result;
    }

      public function scopeHistoryProvider($query, $provider_id,$historyStatus)
    {
        return $query->where('provider_id', $provider_id)
                    ->whereIn('status',$historyStatus)
                    ->orderBy('created_at','desc');
    }

    public function service()
    {
       return $this->belongsTo('App\Models\Service\Service', 'service_id');
    }
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceCategory','service_category_id');
    }
   /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

    public function chat()
    {
       return $this->hasOne('App\Models\Common\Chat', 'request_id');
    }
    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function service_type()
    {
        return $this->belongsTo('App\Models\Common\ProviderService', 'provider_id', 'provider_id');
    }

    public function user_request()
    {
        return $this->belongsTo('App\Models\Common\UserRequest', 'id', 'request_id');
    }

    /**
     * UserRequestPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\Models\Service\ServiceRequestPayment', 'service_request_id');
    }
    public function rating()
    {
        return $this->hasOne('App\Models\Common\Rating', 'request_id');
    }

    /*public function getCreatedAtAttribute() {
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format('Y-m-d H:i:s') : '' ;
        
    }*/
    // ->format('d-m-Y g:i A') : '' ;
    public function getAssignedTimeAttribute() {
        return (isset($this->attributes['assigned_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['assigned_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getScheduleTimeAttribute() {
        return (isset($this->attributes['schedule_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['schedule_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getStartedTimeAttribute() {
        return (isset($this->attributes['started_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['started_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getFinishedTimeAttribute() {
        return (isset($this->attributes['finished_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['finished_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }
     public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
               ->whereNotIn('status' , ['CANCELLED', 'COMPLETED', 'SCHEDULED'])
               ->where('user_rated', 0);
    }

	public function scopeServiceRequestStatusCheck($query, $user_id, $check_status)
	{
		return $query->where('service_requests.user_id', $user_id)
					->where('service_requests.user_rated',0)
					->whereNotIn('service_requests.status', $check_status)
					->select('service_requests.*')
					->with('user','provider','service','payment', 'chat');
	}

	public function scopeServiceRequestAssignProvider($query, $user_id, $check_status)
    {
        return $query->where('service_requests.user_id', $user_id)
                    ->where('service_requests.user_rated',0)
                    ->where('service_requests.provider_id',0)
                    ->whereIn('service_requests.status', $check_status)
                    ->select('service_requests.*');
                    //->with('filter');
    }
    public function scopeHistoryUserTrips($query, $user_id,$showType='')
    {
        if($showType !=''){
        if($showType == 'past'){
            $history_status = array('CANCELLED','COMPLETED');
        }else if($showType == 'upcoming'){
            $history_status = array('SCHEDULED');
        }else{
            $history_status = array('SEARCHING','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED');
        }
        return $query->where('service_requests.user_id', $user_id)
                    ->whereIn('service_requests.status',$history_status)
                    ->orderBy('service_requests.created_at','desc');
        }else{
            
        }
    }

    

     public function scopeServiceSearch($query, $searchText='') {
        return $query->
            whereHas('payment',function($q) use ($searchText){
            $q->where('payment_mode', 'like', "%" . $searchText . "%");
            })
            ->OrwhereHas('service',function($q) use ($searchText){
            $q->where('service_name', 'like', "%" . $searchText . "%");
            })
            ->Orwhere('booking_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%") ;
            
    }

    public function scopeUserUpcomingTrips($query, $user_id)
    {
        return $query->where('service_requests.user_id', $user_id)
                    ->where('service_requests.status', 'SCHEDULED')
                    ->orderBy('service_requests.created_at','desc');
    }
    public function dispute()
    {
        return $this->belongsTo('App\Models\Service\ServiceRequestDispute','id','service_request_id');
    }

    public function getCreatedTimeAttribute() {
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format('d-m-Y g:i A') : '' ;
        
    }
}
