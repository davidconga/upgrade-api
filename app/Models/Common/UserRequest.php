<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use App\Models\Common\State;
use Carbon\Carbon;

class UserRequest extends BaseModel
{
    protected $connection = 'common';

	protected $appends = ['request', 'scheduled_date_time'];

	protected $hidden = [
     	'request_data', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];
    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

   

    public function service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function getRequestAttribute() {
        return json_decode($this->attributes['request_data']);
        
    }

    public function getScheduledDateTimeAttribute() {
        
        $schedule_date = '';
        $timezone = State::find($this->user->state_id)->timezone;
        if ($this->attributes['schedule_at'] != '') {
            $schedule_date = (Carbon::createFromFormat('Y-m-d H:i:s', ($this->attributes['schedule_at']), 'UTC'))->setTimezone($timezone);
            $schedule_date = date('d/m/Y H:i:s', strtotime($schedule_date));
        }
        return $schedule_date;
        
    }
}
