<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
	
    protected $table = 'payments';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamp = true;
	
	public function UserService() 
	{
		return $this->belongsTo('App\Models\UserService' , 'user_service_id');
	}
 
}
