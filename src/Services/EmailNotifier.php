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
        $recipientCount = 0;
        foreach ($notification->getRecipients() as $recipient) {
            if ($recipientCount == 0) {
                $this->mailer->addAddress($recipient);
            } else {
                $this->mailer->addBCC($recipient);
            }
            $recipientCount++;
        }

        $this->mailer->Subject = $notification->getSubject();
        $this->mailer->Body = $notification->getContent();
        $this->mailer->send();

        return;
    }
}
