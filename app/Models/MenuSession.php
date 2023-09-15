<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuSession extends Model
{
	//Table name
    protected $table = 'menu_session';
    //primary key
    public $primaryKey = 'id';
    //timestamps
    public $timestamps = false;
}
