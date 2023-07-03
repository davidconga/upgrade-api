<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class AuthLog extends Model
{
	protected $connection = 'common';
	
    protected $fillable = [
        'user_type', 'user_id', 'type', 'data'
    ];

    public function getDataAttribute() {
        return json_decode($this->attributes['data']) ;
        
    }
}
