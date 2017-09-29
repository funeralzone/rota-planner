<?php

namespace ChrisHarrison\RotaPlanner\Controllers;

use ChrisHarrison\ControllerBuilder\ControllerInterface;
use ChrisHarrison\RotaPlanner\Model\Rota;
use Philo\Blade\Blade;

class RotaController implements ControllerInterface
{
    private $rota;
    private $blade;

    public function __construct(Rota $rota, Blade $blade)
    {
        $this->rota = $rota;
        $this->blade = $blade;
    }

    public function name() : string
    {
        return 'rota';
    }

    public function render() : string
    {
        return $this->blade->view()->make('rota', [
            'pageTitle' => 'Rota',
            'rota' => $this->rota
        ]);
    }
}
