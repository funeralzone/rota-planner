<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use Carbon\Carbon;
use ChrisHarrison\ControllerBuilder\ControllerBuilderInterface;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\Rota;
use ChrisHarrison\RotaPlanner\Model\Services\RotaGenerator;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;
use ChrisHarrison\RotaPlanner\Model\Repositories\MemberRepositoryInterface;
use ChrisHarrison\RotaPlanner\Model\Repositories\RotaRepositoryInterface;
use ChrisHarrison\RotaPlanner\Presenters\RotaPresenter;
use ChrisHarrison\RotaPlanner\Services\Notification;
use ChrisHarrison\RotaPlanner\Services\Notifier;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ChrisHarrison\TimetasticAPI\Client as TimetasticClient;

class BuildCommand extends Command
{
    private $rotaGenerator;
    private $rotaRepository;
    private $memberRepository;
    private $rotaPresenter;
    private $timetasticClient;
    private $blade;
    private $notifier;

    private const TIMESLOT_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function __construct(
        RotaGenerator $rotaGenerator,
        RotaRepositoryInterface $rotaRepository,
        MemberRepositoryInterface $memberRepository,
        RotaPresenter $rotaPresenter,
        TimetasticClient $timetasticClient,
        Blade $blade,
        Notifier $notifier
    )
    {
        $this->rotaGenerator = $rotaGenerator;
        $this->rotaRepository = $rotaRepository;
        $this->memberRepository = $memberRepository;
        $this->rotaPresenter = $rotaPresenter;
        $this->timetasticClient = $timetasticClient;
        $this->blade = $blade;
        $this->notifier = $notifier;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build');
        $this->setDescription('Generate a rota.');
        $this->addArgument('date', InputArgument::OPTIONAL, 'The day to run the command for. Defaults to today.');
        $this->addArgument('daysToBuild', InputArgument::OPTIONAL, 'Days of the week to build.', 'Saturday, Sunday, Friday');
        $this->addOption('team', 't', InputOption::VALUE_REQUIRED, 'An optional team (specified as config file name, ie "fz")');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $when = Carbon::createFromFormat('Y-m-d', $input->getArgument('date') ?? Carbon::now()->format('Y-m-d'));

        $daysToBuild = explode(',', str_replace(' ', '', $input->getArgument('daysToBuild')));

        if (!in_array($when->format('l'), $daysToBuild)) {
            //Not the correct day to build
            $output->writeln('<error>No rota built as it is not ' . $input->getArgument('daysToBuild') . '.</error>');
            return;
        }

        $firstDateOfWeek = $when->startOfWeek()->addWeek();

        $existingRota = $this->rotaRepository->getRotaByName($firstDateOfWeek->format('Y-m-d'));

        if ($existingRota) {
            $output->writeln('<error>Generation already complete for week beginning '. $firstDateOfWeek->format('Y-m-d') .'</error>');
            return;
        }

        $timeSlots = new TimeSlotCollection;
        foreach (static::TIMESLOT_NAMES as $timeSlotName) {
            $timeSlots = $timeSlots->add(new TimeSlot($timeSlotName));
        }

        $team = $this->getTeam($input);

        $members = $this->getMembersWithHolidayEntitlement(
            $firstDateOfWeek,
            $timeSlots,
            $output,
            $team['members']
        );

        $generatedRotaArtifact = $this->rotaGenerator->generate(
            $firstDateOfWeek->format('Y-m-d'),
            $timeSlots,
            $members,
            1
        );

        $rota = $generatedRotaArtifact->getRota();

        $this->updateContributionScores($generatedRotaArtifact->getScoredMembers());
        $this->updateRota($rota);
        $output->writeln('<info>Generation complete and persisted.</info>');
        $this->notify(
            $rota,
            array_unique(array_merge($team['members'], $team['notify']))
        );
        $output->writeln('<info>Notifications sent.</info>');
    }

    private function getMembersWithHolidayEntitlement(
        Carbon $firstDateOfWeek,
        TimeSlotCollection $timeSlots,
        OutputInterface $output,
        ?array $whitelistedMembers
    ) : MemberCollection
    {
        $members = $this->memberRepository
            ->getAllMembers()
            ->filterByTimeTasticIdWhiteList($whitelistedMembers);

        $membersWithEntitlement = new MemberCollection;
        $members->each(function (Member $member) use (&$membersWithEntitlement, $firstDateOfWeek, $timeSlots, $output) {

            if ($member->getTimetasticId() == null) {
                $membersWithEntitlement = $membersWithEntitlement->add($member);
                return;
            }

            $restrictedTimeSlots = $member->getRestrictedTimeSlots();

            $output->writeln('<info>Getting holiday entitlement for ' . $member->getName() . '</info>');

            $holidaysResponse = $this->timetasticClient->getHolidays([
                'userids' => $member->getTimetasticId(),
                'start' => $firstDateOfWeek->format('Y-m-d'),
                'end' => $firstDateOfWeek->copy()->addDays(5)->format('Y-m-d'),
                'status' => 2 /* Approved */
            ]);

            $holidays = json_decode($holidaysResponse->getBody()->getContents(), true)['holidays'];

            $timeSlots->each(function (TimeSlot $timeSlot) use (&$restrictedTimeSlots, $holidays, $firstDateOfWeek) {
                $timeSlotDate = Carbon::instance((new \DateTime)->setISODate(
                    $firstDateOfWeek->year,
                    $firstDateOfWeek->weekOfYear,
                    array_search($timeSlot->getName(), static::TIMESLOT_NAMES)+1
                ));
                foreach ($holidays as $holiday) {
                    $startDate = Carbon::instance(new \DateTime($holiday['startDate']));
                    $endDate = Carbon::instance(new \DateTime($holiday['endDate']))->addHours(23)->addMinutes(59)->addSeconds(59);

                    if ($timeSlotDate->between($startDate, $endDate)) {
                        //Member is on holiday during this timeSlot
                        $restrictedTimeSlots = $restrictedTimeSlots->add($timeSlot);
                        break;
                    }
                }
            });

            $entitledMember = new Member(
                $member->getId(),
                $member->getTimetasticId(),
                $member->getName(),
                $member->getEmail(),
                $restrictedTimeSlots,
                $member->getContributionScore()
            );
            $membersWithEntitlement = $membersWithEntitlement->add($entitledMember);

        });
        return $membersWithEntitlement;
    }

    private function updateContributionScores(MemberCollection $members) : void
    {
        $members->each(function (Member $member) {
            $existingMember = $this->memberRepository->getMemberById($member->getId());
            $existingMember = $existingMember->withContributionScore($member->getContributionScore());
            $this->memberRepository->putMember($existingMember);
        });

        return;
    }

    private function updateRota(Rota $rota) : void
    {
        $this->rotaRepository->putRota($rota);
        return;
    }

    private function notify(
        Rota $rota,
        ?array $whitelistedMembers
    )
    {
        $recipients = [];
        $allMembers = $this->memberRepository
            ->getAllMembers()
            ->filterByTimeTasticIdWhiteList($whitelistedMembers);

        $allMembers->each(function (Member $member) use (&$recipients) {
            $recipients[] = $member->getEmail();
        });

        $startDate = Carbon::instance($this->rotaPresenter->getStartDate($rota));

        $notification = new Notification(
            $recipients,
            'ROTA: Next week\'s Funeral Zone Support (Mon '.$startDate->format('j M').')',
            $this->blade->view()->make('new-rota-email', [
                'rota' => $rota
            ])
        );
        $this->notifier->notify($notification);
    }

    /**
     * @param InputInterface $input
     *
     * @return array|string
     */
    private function getTeam(InputInterface $input)
    {
        $team = [
            'members' => [],
            'notify'  => []
        ];

        if (!is_null($input->getOption('team'))) {
            $team = require __DIR__ . '/../../data/teams/' . $input->getOption('team') . '.php';
        }

        return $team;
    }
}
