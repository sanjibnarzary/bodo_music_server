<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Validator;

class NewUserController extends Controller
{
    public function register(Request $request)
    {
        $data['name'] = $request->input('name');
        $data['email'] = $request->input('email');
        $data['password'] = bcrypt($request->input('password'));

        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = User::create($data);
        $success = "User created successfully";
        $successStatus = '200';

        return response()->json(['success' => $success, 'status' => '200'], $successStatus);
    }
}
