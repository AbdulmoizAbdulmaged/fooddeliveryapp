<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Session;

class AuthManager extends Controller
{
    function login(){
        return view('login');
    }
    function loginPost(Request $request){   
        $this->validate($request,[
            'email'=>'required|email',
            'password'=>'required'
        ]);
     $credentials = $request->only('email', 'password');
        if (auth()->attempt($credentials)) {
            return redirect()->intended(route('dashboard'))->with('success','longin success');
}
return redirect()->intended(route('login'))->with('error','login failed');
}

function logout(){
    Session::flush();
    Auth::logout();
    return redirect()->route('login');
}
}
