<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class AdminCard extends BaseModel
{
	protected $connection = 'common';
	
    public function scopeSearch($query, $searchText='') {
        return $query->where('brand', 'like', "%" . $searchText . "%")
            ->orWhere('last_four', 'like', "%" . $searchText . "%") ;
            
    }
}
