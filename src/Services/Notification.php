<?php

namespace ChrisHarrison\RotaPlanner\Services;

final class Notification
{
    private $recipients;
    private $subject;
    private $content;

    public function __construct(array $recipients, string $subject, string $content)
    {
        $this->recipients = $recipients;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
