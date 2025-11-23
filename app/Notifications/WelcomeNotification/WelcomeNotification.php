<?php
namespace App\Notifications\WelcomeNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    protected $name;
    protected $organizationName;
    protected $logoUrl; 
    protected $organizationFactoryName;

    public function __construct($name , $organizationName, $logoUrl, $organizationFactoryName)
    {
        $this->name             = $name;
        $this->organizationName = $organizationName;
        $this->logoUrl          = $logoUrl;
        $this->organizationFactoryName = $organizationFactoryName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to the ' . $this->organizationName . ' Group!')
            ->markdown('mail.welcome.welcome-notification', [
                'name'             => $this->name,
                'organizationName' => $this->organizationName,
                'logoUrl'          => $this->logoUrl,
                'organizationFactoryName' => $this->organizationFactoryName
            ]);
    }
}
