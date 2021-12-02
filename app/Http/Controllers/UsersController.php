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
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $validated = $validator->validated();
        $validated = json_encode($validated);

        return $validated;
        // $user = new User();

        // $user->name = $validated[name];
        // $user->email = $data->email;
        // $user->password = $data->password;
        // $user->salary = $data->salary;
        // $user->role = $data->role;

    }
}
