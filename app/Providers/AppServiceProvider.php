<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {

            // Si hay sesión, aplicar credenciales dinámicas del ROLE en esta request
            if (session()->has('db_user') && session()->has('db_pass')) {
                $user = session('db_user');
                $pass = Crypt::decryptString(session('db_pass'));

                config([
                    'database.connections.pgsql.host'     => env('DB_HOST'),
                    'database.connections.pgsql.port'     => env('DB_PORT', 5432),
                    'database.connections.pgsql.database' => env('DB_DATABASE'),
                    'database.connections.pgsql.username' => $user,
                    'database.connections.pgsql.password' => $pass,
                ]);

                DB::purge('pgsql');
                DB::reconnect('pgsql');
            }

        } catch (\Throwable $e) {
            // Si algo sale mal, borra sesión y manda a login
            session()->forget(['db_user', 'db_pass']);
            redirect('/login')->send();
            return;
        }
    }
}
