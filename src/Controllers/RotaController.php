<?php

namespace ChrisHarrison\RotaPlanner\Controllers;

use Carbon\Carbon;
use ChrisHarrison\ControllerBuilder\ControllerInterface;
use ChrisHarrison\RotaPlanner\Model\Rota;
use ChrisHarrison\RotaPlanner\Presenters\RotaPresenter;
use Philo\Blade\Blade;

class RotaController implements ControllerInterface
{
    private $rota;
    private $rotaPresenter;
    private $blade;

    public function __construct(Rota $rota, RotaPresenter $rotaPresenter, Blade $blade)
    {
        $this->rota = $rota;
        $this->rotaPresenter = $rotaPresenter;
        $this->blade = $blade;
    }

    public function name() : string
    {
        return strtolower($this->rota->getName());
    }

    public function render() : string
    {
        $startDate = Carbon::instance($this->rotaPresenter->getStartDate($this->rota));
        $endDate = $startDate->copy()->addDays(5);
        $title = 'Rota ' . $startDate->format('j M y') . ' - ' . $endDate->format('j M y');

        return $this->blade->view()->make('rota', [
            'pageTitle' => $title,
            'rota' => $this->rota,
            'rotaPresenter' => $this->rotaPresenter
        ]);
    }
}
