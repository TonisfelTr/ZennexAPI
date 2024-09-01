<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors(),
                'status' => false
                                    ], 400);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('ZemmaxApp')->accessToken;
        $success['name'] = $user->name;

        return response()->json([
            'messages' => 'User register successfully',
            'status' => true,
            'data' => $success
                                ]);
    }

    public function login(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                                        'messages' => $validator->errors(),
                                        'status' => false
                                    ], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('ZemmaxApp')->accessToken;
            $success['name'] = $user->name;

            return response()->json([
                                        'messages' => 'User login successfully',
                                        'status' => true,
                                        'data' => $success
                                    ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => false
                                    ], 401);
        }
    }
}
