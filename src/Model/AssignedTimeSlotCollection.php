<?php

namespace ChrisHarrison\RotaPlanner\Model;

use Collections\Collection;

class AssignedTimeSlotCollection extends Collection
{
    public function __construct()
    {
        parent::__construct(AssignedTimeSlot::class);
    }
}
