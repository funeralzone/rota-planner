<?php

namespace ChrisHarrison\RotaPlanner\Model\Repositories;

use ChrisHarrison\RotaPlanner\Model\Rota;

interface RotaRepositoryInterface
{
    public function getRotaById(string $id) : ?Rota;
    public function getRotaByName(string $name) : ?Rota;
    public function putRota(Rota $rota) : void;
}
