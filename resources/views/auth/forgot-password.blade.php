<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-content">
            <img src="{{ asset('img/logo-jlo.png') }}" class="auth-logo" alt="Municipalidad José Leonardo Ortiz">

            <div class="auth-box">
                <h2 class="auth-title">Recuperar contraseña</h2>
                <p class="auth-subtitle">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group-custom">
                        <label for="email">Correo electrónico</label>
                        <input id="email" class="form-control-custom" type="email" name="email"
                            value="{{ old('email') }}" placeholder="Ingrese correo electrónico" required autofocus>
                    </div>

                    <div class="auth-footer">
                        <a class="auth-link" href="{{ route('login') }}">
                            Volver al inicio de sesión
                        </a>

                        <button type="submit" class="btn-auth">
                            ENVIAR ENLACE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
