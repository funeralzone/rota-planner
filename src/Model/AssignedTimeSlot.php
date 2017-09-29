<?php

namespace ChrisHarrison\RotaPlanner\Model;

class AssignedTimeSlot
{
    private $timeSlot;
    private $assignees;

    public function __construct(TimeSlot $timeSlot, MemberCollection $assignees)
    {
        $this->timeSlot = $timeSlot;
        $this->assignees = $assignees;
    }

    public function getTimeSlot() : TimeSlot
    {
        return $this->timeSlot;
    }

    public function getAssignees() : MemberCollection
    {
        return $this->assignees;
    }
}
