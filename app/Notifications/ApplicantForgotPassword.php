<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ApplicantForgotPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', 'http://localhost:3000' . $resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }

    protected function resetUrl($notifiable)
    {
        $route = 'password.reset';
        $parameters = ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()];
    
        // Generate the URL without the signature
        $urlWithoutSignature = URL::route($route, $parameters, false);
    
        // Remove the base URL
        $baseUrl = config('app.url');
        $relativeUrl = str_replace($baseUrl, '', $urlWithoutSignature);
    
        return $relativeUrl;
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
