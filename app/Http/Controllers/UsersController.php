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

        // Comprueba los datos que se tienen que introducir
        $validator = Validator::make(json_decode($data, true), [
            'email' => 'required',
            'password' => 'required',
        ]);

        //Si falla muestra el erorr
        if ($validator->fails()) {
            $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
        } else {
            $response = ['status'=>1, 'msg'=>''];

            $data = json_decode($data);

            try {

                $user = User::where('email', $data->email)->first();    //Comrpueba si el usuario existe

                if($user) {

                    if(Hash::check($data->password, $user->password)) {     //Si existe comprueba la contraseña introducida
                        $token = Hash::make(now().$user->id);

                        $user->api_token = $token;      //Si coincide inicia sesión creando un token
                        $user->save();

                        $response['data'] = $token;
                        $response['msg'] = "Sesión iniciada correctamente.";
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

        //Comprueba los datos introducidos (comprueba que el mail es único y que en el rol has introducido un rol valido)
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

            //Comprueba el formato de la dirección de correo, si funciona comprueba el de la contraseña
            if(preg_match('/^[a-zA-Z0-9.-_]{1,30}@[a-zA-Z0-9]{1,10}\.[a-zA-Z]{2,5}$/', $data->email)) {     //Que comience por una palabra seguida de un @, otra palabra, un punto y el dominio
                if(preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z]{6,30}$/', $data->password)) {        //Al menos un digito, una letra mayuscula, una minuscula, y que tenga al menos 6 digitos
                    try {
                        $user = new User();

                        //Crea el usuario tras haber comprobado todos los datos
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
                } else {
                    $response['msg'] = "La contraseña no cumple los requisitos (>6 caracteres, al menos 1 mayuscula, al menos 1 numero)";
                    $response['status'] = 0;
                }
            } else {
                $response['msg'] = "El formato del email no es válido.";
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
            if($user_auth->role == 'directive') {   //Si es directivo muestra a todos los usuarios
                $users = User::all();
            } else if($user_auth->role == 'human-resources') {      //Si es recursos humanos muestra a los que no son directivos
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
                if($user_auth->role == 'directive') {       //Si el usuario viendo es directivo lo muestra
                    $information = $user_details;

                } else if($user_auth->role == 'human-resources') {

                    if($user_details->role != 'directive') {        //Si es de recursos humanos lo muestra si no es directivo
                        $information = $user_details;
                    } else {
                        $response['msg'] = "No tienes permisos para ver este usuario.";     //Si no muestra un error
                        $response['status'] = 0;
                    }

                } else {
                    $response['msg'] = "No tienes permisos para ver este usuario.";
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
            $response['data'] = $user_auth;     //Muestra el usuario autenitificado (a sí mismo)
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

                $edit = false;
                if($user_auth->role == 'directive') {       //Si el usuario editando es directivo
                    if($user->role == 'directive') {        //Y el usuario editado tambien es directivo
                        if($user->id == $user_auth->id) {       //Si es el mismo lo edita, si no muestra un error
                            $edit = true;
                        } else {
                            $response['msg'] = "No puedes editar este usuario.";
                            $response['status'] = 0;
                        }
                    } else {
                        $edit = true;
                    }
                } else {
                    if($user->role == 'employee') {         //Si el usuario a editar es empleado se edita
                        $edit = true;
                    } else if($user->role == 'human-resources') {
                        if($user->id == $user_auth->id) {       //Si es recursos humanos y es él mismo, se edita
                            $edit = true;
                        } else {
                            $response['msg'] = "No puedes editar este usuario.";
                            $response['status'] = 0;
                        }
                    } else {
                        $response['msg'] = "No puedes editar este usuario.";
                        $response['status'] = 0;
                    }
                }

                if($edit) {
                    //Se comprueban los unicos datos que validar, email único y rol válido
                    $validator = Validator::make(json_decode($dataJ, true), [
                        'email' => 'unique:users|max:255',
                        'role' => 'in:directive,human-resources,employee',       //['directive', 'human-resources', 'employee']
                    ]);

                    if ($validator->fails()) {
                        $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    } else {

                        if(isset($data->name)) {
                            $user->name = $data->name;
                        }
                        if(isset($data->email)) {
                            if(preg_match('/^[a-zA-Z0-9.-_]{1,30}@[a-zA-Z0-9]{1,10}\.[a-zA-Z]{2,5}$/', $data->email)) {
                                $user->email = $data->email;
                            } else {
                                $edit = false;
                            }
                        }
                        if(isset($data->biography)) {
                            $user->biography = $data->biography;
                        }
                        if(isset($data->salary)) {
                            $user->salary = $data->salary;
                        }
                        if(isset($data->role) && $user_auth->role == 'directive') {     //Solo los directivos pueden cambiar los roles de las personas (para que una persona de rrhh no se cambie a directivo)
                            $user->role = $data->role;
                        }

                        if($edit) {
                            $user->save();
                            $response['msg'] = 'User saved';
                        } else {
                            $response['msg'] = "El email introducido no tiene un formato valido.";
                            $response['status'] = 0;
                        }
                    }
                }
            } else {
                $response['msg'] = "Tienes que introducir un user_id para ser editado.";
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

        $dataJ = $req->getContent();
        $data = json_decode($dataJ);

        $user = User::find($data->id);

        try {
            if($user) {
                $password = Str::random(8);     //Crea una contraseña aleatoria
                $user->password = Hash::make($password);        //La codifica
                $user->save();

                Mail::to($user)->send(new Message($password));      //Manda un mail al usuario con su nueva contraseña
                $response['msg'] = "Mensaje enviado correctamente.";
            }
        } catch (\Throwable $th) {
            $response['msg'] = "Se ha producido un error:".$th->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function changePassword(Request $req) {
        $response = ['status'=>1, 'msg'=>''];

        $data = $req->getContent();

        //comprueba los datos introducidos
        $validator = Validator::make(json_decode($data, true), [
            'email' => 'required',
            'old_password' => 'required',
            'password' => 'required',
        ]);

        $data = json_decode($data);

        if ($validator->fails()) {
            $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
        } else {
            try {
                $user = User::where('email', $data->email)->first();        //Busca al usuario por el email
                if($user) {
                    if(Hash::check($data->old_password, $user->password)) {     //Comprueba la contraseña antigua, si coincide guarda la nueva codificada
                        if(preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z]{6,30}$/', $data->password)) {        //Al menos un digito, una letra mayuscula, una minuscula, y que tenga al menos 6 digitos
                            $user->password = Hash::make($data->password);
                            $user->save();

                            $response['msg'] = "Contraseña cambiada correctamente.";
                        } else {
                            $response['msg'] = "La contraseña no cumple los requisitos (>6 caracteres, al menos 1 mayuscula, al menos 1 numero)";
                            $response['status'] = 0;
                        }
                    } else {
                        $response['msg'] = "La contraseña antigua no coincide.";
                        $response['status'] = 0;
                    }
                } else {
                    $response['msg'] = "El usuario no existe";
                    $response['status'] = 0;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "Se ha producido un error:".$th->getMessage();
                $response['status'] = 0;
            }
        }

        return response()->json($response);
    }
}
