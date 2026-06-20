<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-content">
            <img src="{{ asset('img/logo-jlo.png') }}" class="auth-logo" alt="Municipalidad José Leonardo Ortiz">

            <div class="auth-box">
                <h2 class="auth-title">Acceso al Sistema RSU</h2>
                <p class="auth-subtitle">
                    Ingrese sus credenciales para acceder al sistema
                </p>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group-custom">
                        <label for="email">Correo electrónico</label>
                        <input id="email" class="form-control-custom" type="email" name="email"
                            value="{{ old('email') }}" placeholder="Ingrese correo electrónico" required autofocus
                            autocomplete="username">
                    </div>

                    <div class="form-group-custom">
                        <label for="password">Contraseña</label>
                        <input id="password" class="form-control-custom" type="password" name="password"
                            placeholder="Ingrese contraseña" required autocomplete="current-password">
                    </div>

                    <div class="remember-row">
                        <label>
                            <input type="checkbox" name="remember">
                            Mantener sesión activa
                        </label>
                    </div>

                    <div class="auth-footer">
                        <div>
                            @if (Route::has('password.request'))
                                <a class="auth-link" href="{{ route('password.request') }}">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif

                            <br>

                            @if (Route::has('register'))
                                <a class="auth-link" href="{{ route('register') }}">
                                    Crear cuenta
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn-auth">
                            INICIAR SESIÓN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
