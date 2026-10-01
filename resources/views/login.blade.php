<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso — Estación de servicio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,600;0,700;1,700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/despacho.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="topbar-brand">
            <i data-lucide="fuel"></i>
            <h1>CONSULTA Y REGISTRO DE DESPACHO</h1>
        </div>
    </header>

    <div class="login-wrapper">
      <div class="login-imagen"></div>
      <div class="login-form-col">
        <div class="columna-form">
            <h2 class="login-titulo">Acceso de estación</h2>

            @if ($errors->any())
                <div class="login-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <label><i data-lucide="building-2"></i> Código de estación</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" required>

                <label><i data-lucide="lock"></i> Contraseña</label>
                <input type="password" name="password" required>

                <button type="submit"><i data-lucide="log-in"></i> Ingresar</button>
            </form>
        </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>