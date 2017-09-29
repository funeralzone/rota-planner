<?php

namespace ChrisHarrison\RotaPlanner\Model\Services;

use Ramsey\Uuid\Uuid;

class IdGenerator implements IdGeneratorInterface
{
    public function generate() : string
    {
        return Uuid::uuid4();
    }
}
