<?php

namespace ChrisHarrison\RotaPlanner\Presenters;

use ChrisHarrison\RotaPlanner\Model\Rota;

class RotaPresenter
{
    public function getStartDate(Rota $rota) : \DateTimeInterface
    {
        return new \DateTime($rota->getName());
    }
}
