<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    use HasFactory;
	
    protected $table = 'user_services';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamp = true;
	
	public function Payment() 
	{
		return $this->hasMany('App\Models\Payment' , 'user_service_id');
	}
 
}