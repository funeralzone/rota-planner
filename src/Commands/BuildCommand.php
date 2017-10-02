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
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ChrisHarrison\TimetasticAPI\Client as TimetasticClient;
use PHPMailer\PHPMailer\PHPMailer as Mailer;

class BuildCommand extends Command
{
    private $rotaGenerator;
    private $rotaRepository;
    private $memberRepository;
    private $rotaPresenter;
    private $timetasticClient;
    private $blade;
    private $mailer;

    private const TIMESLOT_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function __construct(
        RotaGenerator $rotaGenerator,
        RotaRepositoryInterface $rotaRepository,
        MemberRepositoryInterface $memberRepository,
        RotaPresenter $rotaPresenter,
        TimetasticClient $timetasticClient,
        Blade $blade,
        Mailer $mailer
    )
    {
        $this->rotaGenerator = $rotaGenerator;
        $this->rotaRepository = $rotaRepository;
        $this->memberRepository = $memberRepository;
        $this->rotaPresenter = $rotaPresenter;
        $this->timetasticClient = $timetasticClient;
        $this->blade = $blade;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build');
        $this->setDescription('Generate a rota.');
        $this->addArgument('date', InputArgument::OPTIONAL, 'Date within the week the rota should be created for. Ordinarily the first date of that week.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $inputDate = $input->getArgument('date');
        if ($inputDate == null) {
            $firstDateOfWeek = Carbon::now()->startOfWeek()->addWeek();
        } else {
            $firstDateOfWeek = Carbon::createFromFormat('Y-m-d', $inputDate)->startOfWeek();
        }

        $timeSlots = new TimeSlotCollection;
        foreach (static::TIMESLOT_NAMES as $timeSlotName) {
            $timeSlots = $timeSlots->add(new TimeSlot($timeSlotName));
        }

        $members = $this->getMembersWithHolidayEntitlement($firstDateOfWeek, $timeSlots);

        $generatedRotaArtifact = $this->rotaGenerator->generate(
            $firstDateOfWeek->format('Y-m-d'),
            $timeSlots,
            $members,
            2
        );

        $rota = $generatedRotaArtifact->getRota();

        $this->updateContributionScores($generatedRotaArtifact->getScoredMembers());
        $this->updateRota($rota);

        $this->notify($rota);
    }

    private function getMembersWithHolidayEntitlement(Carbon $firstDateOfWeek, TimeSlotCollection $timeSlots) : MemberCollection
    {
        $members = $this->memberRepository->getAllMembers();

        $membersWithEntitlement = new MemberCollection;
        $members->each(function (Member $member) use (&$membersWithEntitlement, $firstDateOfWeek, $timeSlots) {

            if ($member->getTimetasticId() == null) {
                $membersWithEntitlement = $membersWithEntitlement->add($member);
                return;
            }

            $restrictedTimeSlots = $member->getRestrictedTimeSlots();

            $holidaysResponse = $this->timetasticClient->getHolidays([
                'userids' => [$member->getTimetasticId()],
                'start' => $firstDateOfWeek->format('Y-m-d'),
                'end' => $firstDateOfWeek->copy()->addDays(7)->format('Y-m-d'),
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
        $existingRota = $this->rotaRepository->getRotaByName($rota->getName());
        if ($existingRota) {
            $rota = new Rota(
                $existingRota->getId(),
                $existingRota->getName(),
                $rota->getAssignedTimeSlots()
            );
        }

        $this->rotaRepository->putRota($rota);
        return;
    }

    private function notify(Rota $rota)
    {
        $allMembers = $this->memberRepository->getAllMembers();
        $allMembers->each(function (Member $member) {
            $this->mailer->addAddress($member->getEmail(), $member->getName());
        });

        $startDate = Carbon::instance($this->rotaPresenter->getStartDate($rota));

        $this->mailer->Subject = 'ROTA: Next week\'s rota (Mon '.$startDate->format('j M').')';
        $this->mailer->Body = $this->blade->view()->make('new-rota-email', [
            'rota' => $rota
        ]);
        $this->mailer->send();
    }
}
