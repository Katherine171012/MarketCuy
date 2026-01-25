<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    public function show()
    {
        // Si ya está logueado, manda a la portada
        if (session()->has('db_user') && session()->has('db_pass')) {
            return redirect()->route('home');
        }

        return view('layouts.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $username = trim($request->input('username'));
        $password = (string) $request->input('password');

        try {
            // Setea credenciales dinámicas para probar el ROLE
            config([
                'database.connections.pgsql.host'     => env('DB_HOST'),
                'database.connections.pgsql.port'     => env('DB_PORT', 5432),
                'database.connections.pgsql.database' => env('DB_DATABASE'),
                'database.connections.pgsql.username' => $username,
                'database.connections.pgsql.password' => $password,
            ]);

            DB::purge('pgsql');
            DB::reconnect('pgsql');

            // Si esto no lanza error, el ROLE + password son válidos
            DB::connection('pgsql')->getPdo();

            session([
                'db_user' => $username,
                'db_pass' => Crypt::encryptString($password),
            ]);

            return redirect()->route('home');

        } catch (\Throwable $e) {
            session()->forget(['db_user', 'db_pass']);

            return back()
                ->withErrors(['login' => 'ERROR REAL: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function logout()
    {
        session()->forget(['db_user', 'db_pass']);
        return redirect()->route('login.show');
    }
}
