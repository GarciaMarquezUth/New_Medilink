<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autorizacion Gmail</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #111827; margin: 0; padding: 32px; }
        main { max-width: 760px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 28px; }
        textarea { width: 100%; min-height: 120px; font-family: monospace; font-size: 14px; padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
    <main>
        <h1>Autorizacion Gmail</h1>

        @if ($error)
            <p>Google devolvio este error:</p>
            <p><code>{{ $error }}</code></p>
        @elseif ($refreshToken)
            <p>Autorizacion completada. Copia este refresh token en tu archivo <code>.env</code>:</p>
            <textarea readonly>{{ $refreshToken }}</textarea>
            <p>Variable:</p>
            <p><code>GOOGLE_REFRESH_TOKEN={{ $refreshToken }}</code></p>
        @else
            <p>Google no devolvio refresh token. Vuelve a iniciar la autorizacion desde <code>/google/gmail/redirect</code>.</p>
            <p>Si ya habias autorizado la app, revoca el acceso en tu cuenta Google y vuelve a intentarlo.</p>
        @endif
    </main>
</body>
</html>
