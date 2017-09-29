<?php

namespace ChrisHarrison\RotaPlanner\Model\Services;

use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\Rota;

class GeneratedRotaArtifact
{
    private $rota;
    private $scoredMembers;

    public function __construct(Rota $rota, MemberCollection $scoredMembers)
    {
        $this->rota = $rota;
        $this->scoredMembers = $scoredMembers;
    }

    public function getRota() : Rota
    {
        return $this->rota;
    }

    public function getScoredMembers() : MemberCollection
    {
        return $this->scoredMembers;
    }
}
