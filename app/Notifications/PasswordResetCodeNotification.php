<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    protected $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de Recuperación de Contraseña')
            ->greeting('¡Hola!')
            ->line('Has solicitado restablecer tu contraseña.')
            ->line('Tu código de verificación es:')
            ->line('**' . $this->code . '**')
            ->line('Este código es válido por **10 minutos**.')
            ->line('Si no solicitaste este cambio, ignora este correo.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}