<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailWithToken extends VerifyEmail
{
    use Queueable;

    protected $token;
    protected $password;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $password)
    {
        $this->token = $token;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //link-token-verify
        //   $verificationUrl = url(route('verification.verify', [
        //     'id' => $notifiable->getKey(),
        //     'token' => $this->token,
        // ], false));
        $verificationUrl = 'https://projectm-tuyenvu.duckdns.org/api/verify-email/'. $notifiable->getKey().'/'.$this->token;

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('This email has been created with the password as: '. $this->password)
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
