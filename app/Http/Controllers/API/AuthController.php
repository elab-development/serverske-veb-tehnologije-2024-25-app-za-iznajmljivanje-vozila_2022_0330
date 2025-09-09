<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    #register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Greška pri validaciji',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Korisnik uspešno registrovan',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

     #login
    public function login(Request $request)
    {
        #validacija podataka
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Greška pri validaciji',
                'errors' => $validator->errors()
            ], 422);
        }   
        
        #autentifikacija korisnika
         if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Pogrešno uneto ime i/ili lozinka'
            ], 401); 
        }

        #kreiranje korisnika i tokena
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        #odgovor
        return response()->json([
            'message' => 'Uspešno prijavljivanje!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);

    }

    #logout
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Uspešno odjavljivanje'
        ]);
    }


    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

}
