@extends('layouts.app')

@section('titulo', 'Login - MarketCuy')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-header fw-semibold text-white" style="background:#660404;">
                    Iniciar sesión
                </div>

                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.auth') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Usuario (ROLE)</label>
                            <input type="text"
                                   name="username"
                                   class="form-control"
                                   value="{{ old('username') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-concho w-100">
                            Entrar
                        </button>
                    </form>

                    <div class="mt-3 small text-muted">
                        Este acceso valida directamente contra los roles de la base de datos centralizada.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
