<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
	
    protected $table = 'services';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamp = false;
 	
	public function Option() 
	{
		return $this->hasOne('App\Models\Option' , 'id', 'option_id');
	}
}
