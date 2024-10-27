<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        
        $validator=Validator::make($request->all(),[
            'name'=>['required','string','max:255'],
            'phone'=>['required','string','min:6','max:255'],
            'password' => ['required', Password::defaults()],
        ]);

        if($validator->fails())
        {
            return ApiResponse::sendResponse(422,"Register Validation Errors",$validator->messages()->all());
        }
        $user=User::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
        ]);
        $data['token']=$user->createToken('APIToken')->plainTextToken;
        $data['name']=$user->name;
        $data['phone']=$user->phone;

        return ApiResponse::sendResponse(201,"User Account Created Successfully",$data);
    }

    public function login(Request $request)
    {
        
        $validator=Validator::make($request->all(),[
            'phone'=>['required','string'],
            'password' => ['required'],
        ]);

        if($validator->fails())
        {
            return ApiResponse::sendResponse(422,"Login Validation Errors",$validator->errors());
        }
        if(Auth::attempt(['phone'=>$request->phone,'password'=>$request->password])){
            $user=Auth::user();
            $data['token']=$user->createToken('APIToken')->plainTextToken;
            $data['name']=$user->name;
            $data['phone']=$user->phone;
            return ApiResponse::sendResponse(200,"User Logged In Successfully",$data);
        }else{
        return ApiResponse::sendResponse(401,"User Credentials doesn't exist",null);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200,'Logged Out Successfully',[]); 
    }
}
