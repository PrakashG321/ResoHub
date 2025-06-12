<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class PersonalEmailVerificationNotification extends VerifyEmail
{
    /**
     * Generate the verification URL that points to your React frontend.
     */
    protected function verificationUrl($notifiable)
    {
        $frontendBaseUrl = config('app.frontend_url', 'http://localhost:5173');
        $backendBaseUrl = config('app.url', 'http://localhost:8000');

        // Generate signed URL for backend route
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->personal_email), // Make sure this is personal_email
            ]
        );

        // Replace backend URL base with frontend base, preserving the signature query param
        return str_replace($backendBaseUrl . '/api', $frontendBaseUrl, $signedUrl);
    }

    /**
     * Build the mail message for verification email.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your College Email Address')
            ->line("Your assigned college email is: **{$notifiable->email}**")
            ->line('Please click the button below to verify your college email address and set your password.')
            ->action('Verify Email', $verificationUrl)
            ->line('If you did not request this, no further action is needed.');
    }
}
