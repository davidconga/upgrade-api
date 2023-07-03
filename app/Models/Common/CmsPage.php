<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class CmsPage extends BaseModel
{
    protected $fillable = [
        'page_name', 'description', 'status', 
        'page_name', 'content', 'status', 
    ];
}
