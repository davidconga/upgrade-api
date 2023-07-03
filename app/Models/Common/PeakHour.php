<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use App\Helpers\Helper;
use Carbon\Carbon;

class PeakHour extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_time',
        'end_time',
        'status',
        'company_id',        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    protected $appends = ['started_time','ended_time'];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('start_time', 'like', "%" . $searchText . "%")
            ->orWhere('end_time', 'like', "%" . $searchText . "%");
           
          
    }

    public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }

    public function getStartedTimeAttribute() {
       
        $timezone=isset($this->attributes['timezone']) ? $this->attributes['timezone']:"UTC";
        return (isset($this->attributes['start_time'])) ? (Carbon::createFromFormat('H:i:s', $this->attributes['start_time'], 'UTC'))->setTimezone($timezone)->format('H:i:s') : '' ;
    }

    public function getEndedTimeAttribute() {
        
        $timezone=isset($this->attributes['timezone']) ? $this->attributes['timezone']:"UTC";
        return (isset($this->attributes['end_time'])) ? (Carbon::createFromFormat('H:i:s', $this->attributes['end_time'], 'UTC'))->setTimezone($timezone)->format('H:i:s') : '';
    }

    /*public function getStartedDateAttribute() {
       
        return ( $this->attributes['start_time'] > $this->attributes['end_time'] ) ? Carbon::yesterday()->toDateString(). ' ' .$this->attributes['start_time'] : Carbon::today()->toDateString(). ' ' .$this->attributes['start_time'] ;
    }
    
    public function getEndedDateAttribute() {
        
        return Carbon::today()->toDateString(). ' ' .$this->attributes['end_time'];
    }*/
}
