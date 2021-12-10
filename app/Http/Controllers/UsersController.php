<?php

namespace App\Http\Controllers;

use App\Mail\Message;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function login(Request $req) {
        $data = $req->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
        } else {
            $response = ['status'=>1, 'msg'=>''];

            $data = json_decode($data);

            try {

                $user = User::where('email', $data->email)->first();

                if($user) {

                    if(Hash::check($data->password, $user->password)) {
                        $token = Hash::make(now().$user->id);

                        $user->api_token = $token;
                        $user->save();

                        $response['msg'] = "Sesión iniciada correctamente. (Token: ".$token." )";
                    } else {
                        $response['msg'] = "La contraseña no coincide.";
                        $response['status'] = 0;
                    }

                } else {
                    $response['msg'] = "No hay ningún usuario con ese email.";
                    $response['status'] = 0;
                }

            } catch (\Throwable $th) {
                $response['msg'] = "Se ha producido un error: ".$th->getMessage();
                $response['status'] = 0;
            }


        }
        return response()->json($response);
    }

    public function create(Request $req) {
        $data = $req->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'name' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
            'biography' => 'required',
            'salary' => 'required',
            'role' => 'required|in:directive,human-resources,employee',       //['directive', 'human-resources', 'employee']
        ]);

        if ($validator->fails()) {
            $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
        } else {
            $response = ['status'=>1, 'msg'=>''];

            $data = json_decode($data);

            try {
                $user = new User();

                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = Hash::make($data->password);
                $user->biography = $data->biography;
                $user->salary = $data->salary;
                $user->role = $data->role;

                $user->save();

                $response['msg'] = "Usuario creado correctamente con el id ".$user->id;
            } catch (\Throwable $th) {
                $response['msg'] = "Se ha producido un error:".$th->getMessage();
                $response['status'] = 0;
            }


        }
        return response()->json($response);
    }

    public function view(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $data = $req->getContent();
        $data = json_decode($data);

        $user_auth = $req->user;

        try {

            if($user_auth->role == 'directive') {
                $users = User::all();
            } else if($user_auth->role == 'human-resources') {
                $users = User::where('role', '<>', 'directive')->get();
            }

            $response['data'] = $users;
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function viewDetails(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $data = $req->getContent();
        $data = json_decode($data);

        $user_auth = $req->user;

        try {

            $user_details = User::where('id', $data->user_id)->first();

            $user_details->makeVisible('email', 'biography');

            if($user_details) {
                if($user_auth->role == 'directive') {
                    $information = $user_details;

                } else if($user_auth->role == 'human-resources') {

                    if($user_details->role != 'directive') {
                        $information = $user_details;
                    } else {
                        $response['msg'] = "You don't have permissions to view this user.";
                        $response['status'] = 0;
                    }

                } else {
                    $response['msg'] = "You don't have permissions to view this user.";
                    $response['status'] = 0;
                }

                if(isset($information)) {
                    $response['data'] = $information;
                }
            } else {
                $response['msg'] = "User id doesn't exist.";
                $response['status'] = 0;
            }

        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function profile(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $data = $req->getContent();
        $data = json_decode($data);

        $user_auth = $req->user;
        $user_auth->makeVisible('email', 'biography');

        try {

            $response['data'] = $user_auth;
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function edit(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $dataJ = $req->getContent();
        $data = json_decode($dataJ);

        $user_auth = $req->user;

        try {
            if(isset($req->user_id)) {

                // Fetch user to edit
                $user = User::find($req->user_id);

                $validator = Validator::make(json_decode($dataJ, true), [
                    'email' => 'unique:users|max:255',
                    'role' => 'in:directive,human-resources,employee',       //['directive', 'human-resources', 'employee']
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                } else {
                    //Añadir compobar usuario que esta editando y si está editando a alguien que puede editar.

                    $response['data'] = 'Funciona';
                }
            } else {
                $response['msg'] = "You have to input a user_id to be edited.";
                $response['status'] = 0;
            }

        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function recover(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $user_auth = $req->user;

        try {
            $user = User::find($user_auth->id);

            if($user) {
                $password = Str::random(7);
                $user->password = Hash::make($password);

                Mail::to($user->mail)->send(new Message($password));
            }
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
