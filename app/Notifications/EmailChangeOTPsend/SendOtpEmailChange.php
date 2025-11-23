<?php
namespace App\Notifications\EmailChangeOTPsend;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpEmailChange extends Notification
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
        $this->otp              = $otp;
        $this->email            = $email;
        $this->name             = $name;
        $this->organizationName = $organizationName;
        $this->logoUrl          = $logoUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your OTP for Email Change')
            ->markdown('mail.email_change.change-email-notification', [
                'otp'              => $this->otp,
                'email'            => $this->email,
                'name'             => $this->name,
                'organizationName' => $this->organizationName,
                'logoUrl'          => $this->logoUrl,
                'organizationFactoryName' => $this->organizationFactoryName
            ]);
    }
}
