<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCodeNotification extends Notification
{
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - Yield AI')
            ->greeting('Bonjour ' . $notifiable->firstname)
            ->line('Vous avez demandé à réinitialiser votre mot de passe.')
            ->line('Voici votre code de vérification :')
            ->line('**' . $this->code . '**') // Code en gras
            ->line('Ce code est valable pendant 30 minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.');
    }
}
