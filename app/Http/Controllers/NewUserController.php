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
        //$data = [];
        $data['name'] = $request->input('name');
        //return $request->input('name');
        //return 'hello';
        //return $request;
        //$data =json_decode($data.toString(), true);
        //print(str($data));
        $data['email'] = $request->input('email');
        $data['password'] = bcrypt($request->input('password'));

        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        /*
        $request['is_admin']=0;
        if ($validator->fails()) { 
             return response()->json(['error'=>$validator->errors()], 401);            
        }
        
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        *
        return UserResource::make($this->userService->createUser(
            $input['name'],
            $input['email'],
            $input['password'],
            $input['is_admin'],
        ));
        */
        //return 'hi';
        $user = User::create($data);
        $success = "User created successfully";
        $successStatus = '200';

        return response()->json(['success' => $success, 'status' => '200'], $successStatus);
    }
}
