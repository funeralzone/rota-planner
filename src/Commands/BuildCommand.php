<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use ChrisHarrison\ControllerBuilder\ControllerBuilderInterface;
use ChrisHarrison\RotaPlanner\Controllers\RotaController;
use ChrisHarrison\RotaPlanner\Model\Services\RotaGenerator;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;
use ChrisHarrison\RotaPlanner\Persistence\MemberRepositoryInterface;
use ChrisHarrison\RotaPlanner\Persistence\RotaRepositoryInterface;
use ChrisHarrison\RotaPlanner\Presenters\RotaPresenter;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private $rotaGenerator;
    private $rotaRepository;
    private $memberRepository;
    private $controllerBuilder;
    private $rotaPresenter;
    private $blade;

    public function __construct(
        RotaGenerator $rotaGenerator,
        RotaRepositoryInterface $rotaRepository,
        MemberRepositoryInterface $memberRepository,
        ControllerBuilderInterface $controllerBuilder,
        RotaPresenter $rotaPresenter,
        Blade $blade
    )
    {
        $this->rotaGenerator = $rotaGenerator;
        $this->rotaRepository = $rotaRepository;
        $this->memberRepository = $memberRepository;
        $this->controllerBuilder = $controllerBuilder;
        $this->rotaPresenter = $rotaPresenter;
        $this->blade = $blade;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build');
        $this->setDescription('Generate a rota.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTimeImmutable;
        $members = $this->memberRepository->getAllMembers();

        $timeSlots = new TimeSlotCollection;
        $timeSlots = $timeSlots->add(new TimeSlot('monday'));
        $timeSlots = $timeSlots->add(new TimeSlot('tuesday'));
        $timeSlots = $timeSlots->add(new TimeSlot('wednesday'));
        $timeSlots = $timeSlots->add(new TimeSlot('thursday'));
        $timeSlots = $timeSlots->add(new TimeSlot('friday'));

        $generatedRotaArtifact = $this->rotaGenerator->generate(
            $date->format('Y') . 'W' . $date->format('W'),
            $timeSlots,
            $members,
            2
        );

        $rota = $generatedRotaArtifact->getRota();

        $this->memberRepository->saveMembersCollection($generatedRotaArtifact->getScoredMembers());
        $this->rotaRepository->putRota($rota);

        $this->controllerBuilder->build(new RotaController($rota, $this->rotaPresenter, $this->blade));
    }
}
