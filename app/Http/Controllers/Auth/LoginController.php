<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
		
        // Validate form data
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required|min:3'
        ]);

        // Attempt to log the user in
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password]))
        {
			return redirect('/ussd');
        }

        // if unsuccessful
        return redirect()->back()->withInput($request->only('email','remember'));
    }
	
	public function logout(Request $request)
	{
		if(\Auth::check())
		{
			\Auth::logout();
			$request->session()->invalidate();
		}
		return  redirect('/');
	}
}