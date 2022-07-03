<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController  extends BaseController
{
    //
    public function signUp(Request $request)
    {
        $request->validate([
            'name'      => 'required|string',
            'dni'       => 'required|string|unique:users|min:8|max:8',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string'
        ]);

        User::create([
            'name'      => $request->name,
            'dni'       => $request->dni,
            'phone'     => $request->phone ?? "",
            'isadmin'   => false,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);

        return $this->sendResponse([], 'Usuario creado correctamente', 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);

        $user = Auth::user();
        $token = $user->createToken('MyApp')->accessToken;
            // var_dump($tokenResult->accessToken->token);
        // if ($request->remember_me)
        //     $token->expires_at = Carbon::now()->addWeeks(1);
        // $token->save();
        $success['user'] = $user;
        $success['token'] =  $token;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User login successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return $this->sendResponse([], 'Cerrar sesión con éxito');
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}