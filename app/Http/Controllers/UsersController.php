<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        try {
            $users = User::all();
            //TODO: check user viewing and show what they can see

            $response['data'] = $users;
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
