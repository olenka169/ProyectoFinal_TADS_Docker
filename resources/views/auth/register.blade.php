<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-content">
            <img src="{{ asset('img/logo-jlo.png') }}" class="auth-logo" alt="Municipalidad José Leonardo Ortiz">

            <div class="auth-box">
                <h2 class="auth-title">Registro de Usuario</h2>
                <p class="auth-subtitle">
                    Complete sus datos para crear una cuenta de acceso al sistema
                </p>

                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group-custom">
                        <label for="name">Nombre completo</label>
                        <input id="name" class="form-control-custom" type="text" name="name"
                            value="{{ old('name') }}" placeholder="Ingrese nombre completo" required autofocus
                            autocomplete="name">
                    </div>

                    <div class="form-group-custom">
                        <label for="email">Correo electrónico</label>
                        <input id="email" class="form-control-custom" type="email" name="email"
                            value="{{ old('email') }}" placeholder="Ingrese correo electrónico" required
                            autocomplete="username">
                    </div>

                    <div class="form-group-custom">
                        <label for="password">Contraseña</label>
                        <input id="password" class="form-control-custom" type="password" name="password"
                            placeholder="Ingrese contraseña" required autocomplete="new-password">
                    </div>

                    <div class="form-group-custom">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" class="form-control-custom" type="password"
                            name="password_confirmation" placeholder="Ingrese nuevamente la contraseña" required
                            autocomplete="new-password">
                    </div>

                    <div class="auth-footer">
                        <a class="auth-link" href="{{ route('login') }}">
                            ¿Ya tienes cuenta?
                        </a>

                        <button type="submit" class="btn-auth">
                            REGISTRAR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
