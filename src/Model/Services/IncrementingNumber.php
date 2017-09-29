<?php

namespace ChrisHarrison\RotaPlanner\Model\Services;

class IncrementingNumber
{
    private $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public function get() : int
    {
        return $this->number++;
    }
}
