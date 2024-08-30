<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;

use App\Models\User;
use App\Models\VerificationCode;
use App\Jobs\SendVerificationCode;



class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $verification_code = Str::upper(Str::random(6));

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $verification_code,
        ]);

        SendVerificationCode::dispatch($user->phone, $verification_code);

        Log::channel('codes')->info('Verification code for user ID :'. $user->id . ', Phone : ' . $user->phone . ', Code : ' . $verification_code);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => ['user' => $user, 'token' => $token],
            'status' => 'success',
            'message' => 'Registration successful! A verification code has been sent to your phone.'
        ], 201);
    }

    



    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();


        if (!$user->is_verified) {
            return response()->json(['message' => 'Account not verified','status' => 'failed'], 403);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials','status' => 'failed'], 401);
        }


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => ['user' => $user, 'token' => $token]
        ]);
    }


    public function verify(VerifyCodeRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if($user->is_verified){
            return response()->json(['message' => 'Account is already verified']);
        }

        $verificationCode = VerificationCode::where('user_id', $user->id)->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'No verification code found'], 404);
        }

        if ($verificationCode->created_at->lt(Carbon::now()->subHour())) {
            return response()->json(['message' => 'Verification code has expired'], 429);
        }

        if ($verificationCode->attempts >= 3) {
            return response()->json(['message' => 'Maximum verification attempts exceeded'], 429);
        }

        if (Str::upper($verificationCode->code) !== $request->code) {
            $verificationCode->increment('attempts');
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->update(['is_verified' => true]);

        $verificationCode->delete();

        return response()->json(['message' => 'Account verified successfully']);
    }


    public function resendVerificationCode(ResendVerificationCodeRequest $request)
    {

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'User is already verified'], 400);
        }

        $verification_code = Str::upper(Str::random(6));

        VerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $verification_code, 'attempts' => 0]
        );

        SendVerificationCode::dispatch($user->phone, $verification_code);

        Log::channel('codes')->info('Verification code for user ID :'. $user->id . ', Phone : ' . $user->phone . ', Code : ' . $verification_code);

        return response()->json(['message' => 'Verification code resent successfully']);
    }





}
