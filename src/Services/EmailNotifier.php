<?php

namespace ChrisHarrison\RotaPlanner\Services;

use PHPMailer\PHPMailer\PHPMailer;

final class EmailNotifier implements Notifier
{
    private $mailer;

    public function __construct(PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(Notification $notification) : void
    {
        $this->mailer->clearAllRecipients();
        foreach ($notification->getRecipients() as $recipient) {
            $this->mailer->addAddress($recipient);
        }

        $this->mailer->Subject = $notification->getSubject();
        $this->mailer->Body = $notification->getContent();
        $this->mailer->send();

        return;
    }
}
