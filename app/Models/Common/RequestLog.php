<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{	
	protected $connection = 'common';
	
    protected $fillable = [
        'data',
    ];

    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];
}
