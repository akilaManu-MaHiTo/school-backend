<?php
namespace App\Notifications\FrogotpasswordOTPsend;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordChangeConfirmation extends Notification
{
    use Queueable;

    protected $otp;
    protected $email;
    protected $name;
    protected $organizationName;
    protected $logoUrl;
    protected $organizationFactoryName;

    public function __construct($otp, $email, $name, $organizationName, $logoUrl, $organizationFactoryName)
    {
        $this->otp                     = $otp;
        $this->email                   = $email;
        $this->name                    = $name;
        $this->organizationName        = $organizationName;
        $this->logoUrl                 = $logoUrl;
        $this->organizationFactoryName = $organizationFactoryName;

    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your OTP for Password Change')
            ->markdown('mail.forgot_password.forgot-password-notification', [
                'otp'              => $this->otp,
                'email'            => $this->email,
                'name'             => $this->name,
                'organizationName' => $this->organizationName,
                'logoUrl'          => $this->logoUrl,
                'organizationFactoryName' => $this->organizationFactoryName
            ]);
    }
}
