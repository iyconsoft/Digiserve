<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    use HasFactory;
	
    protected $table = 'service_options';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamp = false;
 
}
