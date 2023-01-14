<?php

namespace App\Http\Controllers;

use App\Http\Helper\ApiHelper;
use App\Http\Helper\FileHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    /**
     * User registration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6']
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);

        if (!$user->save()) {
            return ApiHelper::response(500, null, 'internal server error');
        }

        if (!File::makeDirectory(FileHelper::getAbsoluteDirPath($user->id), 0777)) {
            $user->delete();
            return ApiHelper::response(500, null, 'couldn\'t create user\'s folder');
        }

        return ApiHelper::response(200, ['token' => $user->createToken('API token')->plainTextToken]);
    }

    /**
     * User login
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6']
        ]);

        if (!Auth::attempt($data)) {
            return ApiHelper::response(401, null, 'incorrect username or password');
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        return ApiHelper::response(200, ['token' => $user->createToken('API token')->plainTextToken]);
    }
}
