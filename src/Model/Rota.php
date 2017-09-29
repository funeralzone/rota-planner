<?php

namespace ChrisHarrison\RotaPlanner\Model;

class Rota
{
    private $id;
    private $name;
    private $assignedTimeSlots;

    public function __construct(string $id, string $name, AssignedTimeSlotCollection $assignedTimeSlots)
    {
        $this->id = $id;
        $this->name = $name;
        $this->assignedTimeSlots = $assignedTimeSlots;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getAssignedTimeSlots() : AssignedTimeSlotCollection
    {
        return $this->assignedTimeSlots;
    }
}
