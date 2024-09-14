<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            $validator = Validator::make($credentials,
                                            [
                                                'email' => 'required|email',
                                                'password' => 'required|min:8'
                                            ]
                                            );
            if($validator->fails()):
                    return response()->json([
                                            'status' => 403,
                                            'error' => $validator->errors(),
                                        ], 403);
            else:
                if (Auth::attempt($credentials)):
                    $user = Auth::user();
                    $token = JWTAuth::fromUser($user);

                    return $this->respondWithToken($token);
                else:
                    return response()->json([
                                        'status' => 403,
                                        'error' => 'Invalid Credentials'
                                    ], 403);
                endif;
            endif;
        }catch(Exception $e){
            // return $e;
            return response()->json([
                                'status' => 500,
                                'error' => 'Something went wrong'
                            ], 500);
        }
    }

    public function logout()
    {
        auth()->logout(true);
        return response()->json([
                                        'status' => 200,
                                        'message' => 'Successfully logged out'
                                    ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDetails()
    {
        return response()->json([
                                    'status' => 200,
                                    'data' => auth()->user(),
                                    'message' => __('notifications.data_found', ['model' => "User Details"])
                                ], 200);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ];

        return response()->json([
            'status' => 200,
            'data' => $data,
        ], 200);
    }
}
