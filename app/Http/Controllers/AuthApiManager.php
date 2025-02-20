<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthApiManager extends Controller
{
    //login function
    function login(Request $request){
    if(empty($request->email) && empty($request->password)){
        return array('status' => 'error', 'message' => 'Email and password are required');
    }
    $user = User::where('email', $request->email)->first();
    if(!$user){
        return array('status' => 'error', 'message' => 'User not found');
    }
    $credentials = $request->only('email', 'password');
    if(auth()->attempt($credentials)){
       return array('status' => 'success', 'message' => 'Login successful',"user"=>$user->name,"email"=>$user->email);
}
    return array('status' => 'error', 'message' => 'Invalid login credentials');
}

//register function
function registeration(Request $request){
    if(empty($request->name) && empty($request->email) && empty($request->password)){
        return "failed";
    }
    $user = User::where('email', $request->email)->first();
    if($user){
        return "failed";
    }
    $user = User::create([
        'type' => 'customer',
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password)
    ]);
    if(!$user){
        return "failed";
    }
    return "success";
}
}