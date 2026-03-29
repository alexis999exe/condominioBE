<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            color: #1a1a1a;
        }
        .wrapper {
            max-width: 580px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 36px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin-top: 6px;
        }
        .body {
            padding: 40px;
        }
        .greeting {
            font-size: 16px;
            color: #374151;
            margin-bottom: 16px;
        }
        .message {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .code-label {
            font-size: 12px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-align: center;
        }
        .code-box {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 32px;
        }
        .code {
            font-size: 48px;
            font-weight: 800;
            color: #dc2626;
            letter-spacing: 12px;
            font-variant-numeric: tabular-nums;
        }
        .expiry {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 10px;
        }
        .expiry strong {
            color: #dc2626;
        }
        .warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            font-size: 13px;
            color: #92400e;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .footer {
            border-top: 1px solid #f3f4f6;
            padding: 24px 40px;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }
        .building-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="building-icon">🏢</div>
            <h1>Sistema de Condominio</h1>
            <p>Recuperación de contraseña</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $userName }}</strong></p>

            <p class="message">
                Recibimos una solicitud para restablecer la contraseña de tu cuenta.
                Usa el siguiente código de 6 dígitos para continuar con el proceso:
            </p>

            <p class="code-label">Tu código de verificación</p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
                <p class="expiry">
                    Válido por <strong>10 minutos</strong>
                </p>
            </div>

            <div class="warning">
                <strong>⚠️ Importante:</strong> Si no solicitaste este código, ignora este correo.
                Tu contraseña no será modificada. Nunca compartas este código con nadie.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este correo fue enviado automáticamente por el Sistema de Condominio.<br>
            Por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>