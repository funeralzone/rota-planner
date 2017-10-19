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
        $body = [
            'Subject: ' . $notification->getSubject(),
            'Recipients: ' . implode(',', $notification->getRecipients()),
            'Body:',
            '------------------------',
            '',
            $notification->getContent()
        ];

        $this->mailer->clearAllRecipients();
        $this->mailer->addAddress($this->testRecipient);
        $this->mailer->Subject = '---TEST---:' . $notification->getSubject();
        $this->mailer->Body = implode(PHP_EOL, $body);
        $this->mailer->send();

        return;
    }
}
