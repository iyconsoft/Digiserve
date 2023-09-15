<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
	
    protected $table = 'options';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamp = true;
	
}
