<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);
    $user = User::where('username', $request->username)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        $token = $user->createToken("Auth");
        return response()->json(["message" => "Berhasil masuk akun", 'token' => $token->plainTextToken]);
    } else {
        return response()->json(["message" => "Gagal masuk akun"], 422);
    }
})
    ->name('login');

Route::post('/register', function (Request $request) {
    try {
        Validator::validate($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash:ascii', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return response()->json(['message' => 'Berhasil membuat akun', 'user' => $user]);
        } else {
            return response()->json(["message" => "Gagal membuat akun"], 500);
        }
    } catch (Exception $e) {
        return response()->json(["message" => "Gagal membuat akun"], 422);
    }
})
    ->name('register');

Route::delete('/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Berhasil keluar akun']);
})
    ->middleware('auth:sanctum')
    ->name('logout');

Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{post}/like', function (Post $post) {
        try {
            $like = Like::where('post_id', $post->id)->where('user_id', auth()->id())->first();

            if (!$like) {
                Like::create(['post_id' => $post->id, 'user_id' => auth()->id()]);
                return response()->json(['message' => 'Berhasil menyukai postingan']);
            } else {
                return response()->json(["message" => "Postingan sudah disukai"], 500);
            }
        } catch (Exception $e) {
            return response()->json(["message" => "Gagal menyukai postingan"], 422);
        }
    });
    Route::post('/posts/{post}/unlike', function (Post $post) {
        try {
            $like = Like::where('post_id', $post->id)->where('user_id', auth()->id())->first();

            if ($like) {
                $like->delete();
                return response()->json(['message' => 'Berhasil menghapus suka postingan']);
            } else {
                return response()->json(["message" => "Gagal menghapus suka postingan"], 500);
            }
        } catch (Exception $e) {
            return response()->json(["message" => "Gagal menghapus suka postingan"], 422);
        }
    });
});
