<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
{
    $url = url('/api/verify-email/' . $notifiable->id);

    return (new MailMessage)
        ->subject('Activation de votre compte Yield AI')
        ->greeting('Bonjour ' . $notifiable->firstname . ' !')
        ->line('Merci de vous être inscrit. Veuillez cliquer sur le bouton ci-dessous pour activer votre compte.')
        ->action('Activer mon compte', $url)
        ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.');
        // La ligne ->thanks() a été supprimée ici
}
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
