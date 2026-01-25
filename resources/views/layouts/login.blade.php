@extends('layouts.app')

@section('titulo', 'Login - MarketCuy')

@section('contenido')
    <div class="card shadow border-0 login-card">
        <div class="login-head">
            <h4 class="title">Iniciar sesión</h4>
        </div>

        <div class="login-body">

            <div class="login-welcome">
                <h5>Bienvenido a MarketCuy</h5>
                <p>Para ingresar al sistema, primero debe iniciar sesión.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger login-error mb-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.auth') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label login-label">Usuario</label>
                    <input type="text"
                           name="username"
                           class="form-control login-input"
                           value="{{ old('username') }}"
                           placeholder="Ej: Jose"
                           autocomplete="username"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label login-label">Contraseña</label>
                    <input type="password"
                           name="password"
                           class="form-control login-input"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                </div>

                <button type="submit" class="btn btn-concho w-100 login-btn">
                    Entrar
                </button>
            </form>
        </div>
    </div>
@endsection
