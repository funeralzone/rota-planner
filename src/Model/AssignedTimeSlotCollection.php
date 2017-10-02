<?php

namespace ChrisHarrison\RotaPlanner\Model;

use Collections\Collection;

class AssignedTimeSlotCollection extends Collection
{
    public function __construct()
    {
        parent::__construct(AssignedTimeSlot::class);
    }

    public function slotByName(string $name) : ?AssignedTimeSlot
    {
        $match = null;
        $this->each(function (AssignedTimeSlot $timeSlot) use (&$match, $name) {
            if ($timeSlot->getTimeSlot()->getName() == $name) {
                $match = $timeSlot;
            }
        });

        return $match;
    }
}
