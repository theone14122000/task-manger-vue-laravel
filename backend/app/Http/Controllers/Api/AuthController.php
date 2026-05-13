<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\User;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {

        // VALIDATION
        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users',

            'password' => 'required|min:6'
        ]);


        // CREATE USER
        $user = User::create([

            'name' => $validated['name'],

            'email' => $validated['email'],

            'password' => bcrypt($validated['password']),
        ]);


        // SEND EMAIL VERIFICATION
        $user->sendEmailVerificationNotification();


        // CREATE TOKEN
        $token = $user->createToken('auth_token')->plainTextToken;


        // RESPONSE
        return response()->json([

            'message' => 'Registered successfully.',

            'token' => $token,

            'user' => $user
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {

        // VALIDATION
        $validated = $request->validate([

            'email' => 'required|email',

            'password' => 'required'
        ]);


        // FIND USER
        $user = User::where('email', $validated['email'])->first();


        // CHECK USER
        if (!$user || !Hash::check($validated['password'], $user->password)) {

            return response()->json([

                'message' => 'Invalid credentials'

            ], 401);
        }
        if (!$user->hasVerifiedEmail()) {

    return response()->json([

        'message' => 'Please verify your email first.'

    ], 403);
}


        // CREATE TOKEN
        $token = $user->createToken('auth_token')->plainTextToken;


        // RESPONSE
        return response()->json([

            'token' => $token,

            'user' => $user
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */

    public function updateProfile(Request $request)
    {

        // GET LOGGED IN USER
        $user = $request->user();


        // VALIDATION
        $request->validate([

            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email,' . $user->id,

            'current_password' => 'nullable',

            'password' => 'nullable|min:6|confirmed',

            'avatar' => 'nullable|image|max:2048'
        ]);


        /*
        |--------------------------------------------------------------------------
        | CHECK CURRENT PASSWORD
        |--------------------------------------------------------------------------
        */

        if ($request->filled('password')) {

            // CURRENT PASSWORD REQUIRED
            if (!$request->filled('current_password')) {

                return response()->json([

                    'message' => 'Current password is required'

                ], 422);
            }

            // PASSWORD CHECK
            if (!Hash::check($request->current_password, $user->password)) {

                return response()->json([

                    'message' => 'Current password is incorrect'

                ], 422);
            }

            // UPDATE PASSWORD
            $user->password = bcrypt($request->password);
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE BASIC INFO
        |--------------------------------------------------------------------------
        */

        $user->name = $request->name;

        $user->email = $request->email;


        /*
        |--------------------------------------------------------------------------
        | AVATAR UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('avatar')) {

            $path = $request->file('avatar')->store('avatars', 'public');

            $user->avatar = $path;
        }


        // SAVE USER
        $user->save();


        // RESPONSE
        return response()->json([

            'message' => 'Profile updated successfully',

            'user' => $user
        ]);
    }
}