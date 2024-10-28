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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // For generating random strings


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:6', 'max:255'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, "Register Validation Errors", $validator->messages()->all());
        }
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);


        // Generate a random 6-digit verification code
        $verificationCode = random_int(100000, 999999); // Generates a random number between 100000 and 999999

        // Save the verification code to the user
        $user->verification_code = $verificationCode;
        $user->save();

        // Log the verification code (you can replace this with sending it via SMS or email)
        Log::info("Verification code for user {$user->name}: {$verificationCode}");


        $data['token'] = $user->createToken('APIToken')->plainTextToken;
        $data['name'] = $user->name;
        $data['phone'] = $user->phone;

        return ApiResponse::sendResponse(201, "User Account Created Successfully", $data);
    }

    public function login(Request $request)
    {
        // Validate login input
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);
    
        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, "Login Validation Errors", $validator->errors());
        }
    
        // Attempt to authenticate user
        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $user = Auth::user();
    
            // Check if the user is verified
            if (!$user->is_verified) {
                return ApiResponse::sendResponse(403, "Account not verified. Please verify to continue.", null);
            }
    
            // Generate token and return user data if verified
            $data['token'] = $user->createToken('APIToken')->plainTextToken;
            $data['name'] = $user->name;
            $data['phone'] = $user->phone;
    
            return ApiResponse::sendResponse(200, "User Logged In Successfully", $data);
        } else {
            return ApiResponse::sendResponse(401, "User credentials are incorrect", null);
        }
    }
    

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200, 'Logged Out Successfully', []);
    }

    public function verifyCode(Request $request)
{
    // Validate the input
    $request->validate([
        'phone' => 'required|string',
        'verification_code' => 'required|integer',
    ]);

    // Find the user by phone
    $user = User::where('phone', $request->phone)->first();

    // Check if user exists and code matches
    if (!$user || $user->verification_code != $request->verification_code) {
        return response()->json(['message' => 'Invalid verification code.'], 422);
    }

    // Update user's verified status and clear the verification code
    $user->is_verified = true;
    $user->verification_code = null;
    $user->save();

    return response()->json(['message' => 'Account verified successfully.'], 200);
}
}
