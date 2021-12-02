<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UsersController extends Controller
{
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
                $user->password = $data->password;
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
            $response['data'] = ;
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
