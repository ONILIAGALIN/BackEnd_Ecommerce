<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     * @return \Illuminate\Http\Response
     */

    public function index(){
        return response()->json([
            "ok" => true,
            "message" => "User Retrieved successfully",
            "data" => User::with("profile")->get()
        ],200);
    }

    /**
     * Creates a user from the inputs from the request.
     * POST::/api/users
     * @param Request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request){
        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:64|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|min:2|max:32',
            'middle_name' => 'sometimes|string|max:32',
            'last_name' => 'required|string|min:2|max:32',
            'address' => 'required|string|max:255',
            'phone_number' => ["required", "string", "min:11", "regex:/^(09|\+639)\d{9}$/", "not_regex:/[a-zA-Z]/"],
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

        return response()->json([
            "ok" => true,
            "message" => "User Account created successfully",
            "data" => $user
        ],201);
    }

    /**
     * Retrieve the specific user using id.
     * Get::/api/users/{users}
     * @param Request
     * @param  User
     * @return \Illuminate\Http\Response
     */

    public function show (Request $request, User $user){
        $user->profile;
        return response()->json([
            "ok" => true,
            "message" => "Specific User Retrieved successfully",
            "data" => $user
        ],200);
    }

    /**
     * Update specific user using the inputs from request and id from the uri
     *PATCH: api/users/{user}
     * @param Request
     * param User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user){
        $validator = validator($request->all(), [
            "username" => "sometimes|string|max:255|unique:users,username,".$user->id,
            "email" => "sometimes|string|email|max:64|unique:users,email,".$user->id,
            "password" => "sometimes|string|min:8|confirmed",
            "first_name" => "sometimes|string|min:2|max:32",
            "middle_name" => "sometimes|string|max:32",
            "last_name" => "sometimes|string|min:2|max:32",
            "address" => "sometimes|string|max:255",
            "phone_number" => "sometimes|string|max:11",
        ]);

        if($validator->fails()){
            return response()->json([
                "ok" => false,
                "message" => "Request didn't pass validation",
                "errors" => $validator->errors()
            ],400);
        }
        $user_input = $validator->safe()->only(["username","email","password"]);
        $profile_input = $validator->safe()->except(["username","email","password"]);
        $user->update($user_input);
        $user->profile()->update($profile_input);
        $user->profile;

        return response()->json([
            "ok" => true,
            "message" => "User Updated successfully",
            "data" => $user
        ],200);
    }

    /**
     * Delete specific user using the id from the uri
     * DELETE: api/users/{user}
     * @param Request
     * @param User
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user){
        $user->delete();
        return response()->json([
            "ok" => true,
            "message" => "User Deleted successfully"
        ],200);
    }
}
