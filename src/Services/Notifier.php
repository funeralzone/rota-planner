<?php

namespace ChrisHarrison\RotaPlanner\Services;

interface Notifier
{
    public function notify(Notification $notification) : void;
}
