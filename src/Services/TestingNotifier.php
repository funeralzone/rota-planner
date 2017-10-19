<?php

namespace ChrisHarrison\RotaPlanner\Services;

use PHPMailer\PHPMailer\PHPMailer;

final class TestingNotifier implements Notifier
{
    private $mailer;
    private $testRecipient;

    public function __construct(PHPMailer $mailer, string $testRecipient)
    {
        $this->mailer = $mailer;
        $this->testRecipient = $testRecipient;
    }

    public function notify(Notification $notification) : void
    {
        $this->mailer->clearAllRecipients();
        $this->mailer->addAddress($this->testRecipient);
        $this->mailer->Subject = '---TEST---:' . $notification->getSubject();
        $this->mailer->Body = 'Test message sent to: ' . implode(',', $notification->getRecipients()) . PHP_EOL.PHP_EOL . 'Content:' . PHP_EOL.PHP_EOL . $notification->getContent();
        $this->mailer->send();

        return;
    }
}
