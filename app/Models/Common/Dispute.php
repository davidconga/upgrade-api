<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Dispute extends BaseModel
{
    protected $connection = 'common';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [      
        'dispute_type',       
        'dispute_name',
        'service',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('dispute_type', 'like', "%" . $searchText . "%")
            ->orWhere('dispute_name', 'like', "%" . $searchText . "%");
    }
}
