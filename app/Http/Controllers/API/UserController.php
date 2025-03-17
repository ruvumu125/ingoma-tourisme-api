<?php

namespace App\Http\Controllers\API;

use App\Helpers\GlobalFunctions;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {

            if ($user->status === "Inactive") {
                return $this->sendError('Unauthorised.', ['error' => 'Votre compte n\'est pas activé']);
            } else {

                $success['token'] = $user->createToken('MyApp')->plainTextToken;
                $success['user'] = $user;
                return $this->sendResponse($success, 'User login successfully.');
            }
        }

        return $this->sendError('Unauthorised.', ['error' => 'Vos identifiants semblent incorrects']);
    }

    public function customerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|max:255|unique:users,phone_number',
            'password' => 'required|min:6|max:100|unique:users,password',
            'confirm_password' => 'required|same:password'

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'role' =>"customer",
            'status' =>"Active"
        ]);

        if($user){

            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] =  $user->first_name;
            $success['last_name'] =  $user->last_name;
            $success['email'] =  $user->email;
            $success['phone_number'] = $user->phone_number;
            $success['role'] = $user->role;
            return $this->sendResponse($success, 'Customer created successfully..');
        }

        return null;
    }

    public function adminRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|max:255|unique:users,phone_number'

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $password=GlobalFunctions::generate_UIID(6);
        $encrypted_password=bcrypt($password);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $encrypted_password,
            'role' =>"admin",
            'status' =>"Active"
        ]);

        if($user){

            $mailData = [
                'nom'=>$request->first_name.' '.$request->last_name,
                'password'=>$password
            ];

            Mail::to($request->email)->send(new SendMail($mailData));

            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] =  $user->first_name;
            $success['last_name'] =  $user->last_name;
            $success['email'] =  $user->email;
            $success['phone_number'] = $user->phone_number;
            $success['role'] = $user->role;
            return $this->sendResponse($success, 'Administrator created successfully..');
        }

        return null;
    }

    public function superAdminRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|max:255|unique:users,phone_number'

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $password=GlobalFunctions::generate_UIID(6);
        $encrypted_password=bcrypt($password);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $encrypted_password,
            'role' =>"superadmin",
            'status' =>"Active"
        ]);

        if($user){

            $mailData = [
                'nom'=>$request->first_name.' '.$request->last_name,
                'password'=>$password
            ];

            Mail::to($request->email)->send(new SendMail($mailData));

            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] =  $user->first_name;
            $success['last_name'] =  $user->last_name;
            $success['email'] =  $user->email;
            $success['phone_number'] = $user->phone_number;
            $success['role'] = $user->role;
            return $this->sendResponse($success, 'Super administrator created successfully..');
        }

        return null;
    }

    // Update an existing user
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|string|max:255',
            'phone_number' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->save();

        return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }
}
