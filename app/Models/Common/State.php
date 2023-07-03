<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class State extends BaseModel
{
	protected $connection = 'common';
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function cities() {
    	return $this->hasMany('App\Models\Common\City');
    }
}
