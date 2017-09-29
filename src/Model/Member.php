<?php

namespace ChrisHarrison\RotaPlanner\Model;

class Member
{
    private $id;
    private $name;
    private $email;
    private $restrictedTimeSlots;
    private $contributionScore;

    public function __construct(string $id, string $name, string $email, TimeSlotCollection $restrictedTimeSlots, int $contributionScore)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->restrictedTimeSlots = $restrictedTimeSlots;
        $this->contributionScore = $contributionScore;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getRestrictedTimeSlots() : TimeSlotCollection
    {
        return $this->restrictedTimeSlots;
    }

    public function getContributionScore() : int
    {
        return $this->contributionScore;
    }

    public function isAvailable(TimeSlot $timeSlot) : bool
    {
        return !$this->restrictedTimeSlots->contains(function (TimeSlot $iTimeSlot) use ($timeSlot) {
            return $iTimeSlot->getName() == $timeSlot->getName();
        });
    }

    public function withContributionScore(int $score) : self
    {
        $this->contributionScore = $score;
        return $this;
    }
}
