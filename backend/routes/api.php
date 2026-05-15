<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/tasks/reorder', [TaskController::class, 'reorder']);

// REGISTER
Route::post('/register', [AuthController::class, 'register']);

// LOGIN
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/

Route::get('/email/verify/{id}/{hash}', function (
    Request $request,
    $id,
    $hash
) {

    // FIND USER
    $user = User::find($id);

    // USER NOT FOUND
    if (!$user) {

        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // CHECK HASH
    if (!hash_equals(
        sha1($user->getEmailForVerification()),
        $hash
    )) {

        return response()->json([
            'message' => 'Invalid verification link'
        ], 403);
    }

    // VERIFY EMAIL
    if (!$user->hasVerifiedEmail()) {

        $user->markEmailAsVerified();
    }

    return response()->json([
        'message' => 'Email verified successfully'
    ]);

})->middleware('signed')->name('verification.verify');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    // CURRENT USER
    Route::get('/me', function (Request $request) {

        return $request->user();
    });

    // LOGOUT
    Route::post('/logout', function (Request $request) {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | TASKS
    |--------------------------------------------------------------------------
    */

    // GET USER TASKS
    Route::get('/tasks', [TaskController::class, 'index']);

    // CREATE TASK
    Route::post('/tasks', [TaskController::class, 'store']);

    // UPDATE TASK
    Route::put('/tasks/{task}', [TaskController::class, 'update']);

    // DELETE TASK
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // TOGGLE TASK
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle']);


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    // UPDATE PROFILE
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);

    // UPLOAD AVATAR
    Route::post('/upload-avatar', function (Request $request) {

        $request->validate([
            'avatar' => 'required|image|max:2048'
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $request->user();

        $user->avatar = $path;

        $user->save();

        return response()->json([
            'avatar' => $path
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | RESEND VERIFICATION EMAIL
    |--------------------------------------------------------------------------
    */

    Route::post('/email/verification-notification', function (
        Request $request
    ) {

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent'
        ]);

    })->middleware('throttle:6,1');

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // ALL USERS
    Route::get('/admin/users', function () {

        return User::latest()->get();
    });

    // ALL TASKS
    Route::get('/admin/tasks', [TaskController::class, 'adminTasks']);

});