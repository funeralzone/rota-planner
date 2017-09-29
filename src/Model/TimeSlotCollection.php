<?php

namespace ChrisHarrison\RotaPlanner\Model;

use Collections\Collection;

class TimeSlotCollection extends Collection
{
    public function __construct()
    {
        parent::__construct(TimeSlot::class);
    }
}
