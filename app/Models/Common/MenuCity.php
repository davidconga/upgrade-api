<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class MenuCity extends BaseModel
{
	protected $connection = 'common';

	public function menus()
    {
        return $this->hasone('App\Models\Common\Menu', 'id', 'menu_id');
    }

   
}
