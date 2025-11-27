<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Validator;
class AuthController extends Controller
{
    /**
     * Creates a user from the inputs from the request.
     * POST::/api/register
     * @param Request
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request){
        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:64|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'sometimes|string|max:32',
            'last_name' => 'required|string|min:2|max:255',
            'address' => 'required|string|max:500',
            'phone_number' => 'required|string|max:20',
        ]);
        
        if($validator->fails()){
            return response()->json([
                "ok" => false,
                "message" => "Request didn't pass validation",
                "errors" => $validator->errors()
            ],400);
        }

        $user_input = $validator->safe()->only(["name","email","password"]);
        $profile_input = $validator->safe()->except(["name","email","password"]);

        $user = User::create($user_input);
        $user->profile()->create($profile_input);
        $user->profile;
        $user->token = $user->createToken("api_token")->accessToken;

        return response()->json([
            "ok" => true,
            "message" => "User registered successfully",
            "data" => $user
        ],201);
    }

     /**
    * Login using the inputs from request
    *POST: /api/login //URL need to access
    * @param Request 
    *@return \Illuminate\Http\Response
    */

    public function login (Request $request){
        $validator = Validator($request->all(), [
            "name" => "required",
            "password" => "required",
        ]);

        if(!auth()->attempt($validator->validated())){
            return response()->json([
                "ok" => false,
                "message" => "Invalid Credentials!"
            ],401);
        }

        $user = auth()->user();
        $user->profile;

        $user->token = $user->createToken("login_token")->accessToken;

        return response()->json([
            "ok" => true,
            "message" => "Login Succesfully.",
            "data" => $user
        ],200);
    }

    /**
     * Retrieve the UserInfo using berrer token
     * *GET: /api/checkToken
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function checkToken(Request $request){
        $user = $request->user();
        $user->profile;
        return response()->json([
            "ok" => true,
            "message" => "UserInfo has been retrieve!",
            "data" => $user
        ],200);
    }
}
