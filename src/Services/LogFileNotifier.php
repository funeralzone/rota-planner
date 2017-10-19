<?php

namespace ChrisHarrison\RotaPlanner\Services;

final class LogFileNotifier implements Notifier
{
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
        file_put_contents('mail.txt', implode(PHP_EOL, $body) . PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL, FILE_APPEND);

        return;
    }
}
