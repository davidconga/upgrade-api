<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reason extends BaseModel
{
    protected $connection = 'common';

     
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type','reason','status','service','lang'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'company_id', 'created_at', 'updated_at'
    ];


     public function scopeSearch($query, $searchText='') {
        return $query
            ->where('type', 'like', "%" . $searchText . "%")
            ->orWhere('reason', 'like', "%" . $searchText . "%") 
            ->orWhere('status', 'like', "%" . $searchText . "%");
    }

  
}
