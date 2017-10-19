<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use Carbon\Carbon;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\Repositories\RotaRepositoryInterface;
use ChrisHarrison\RotaPlanner\Services\Notification;
use ChrisHarrison\RotaPlanner\Services\Notifier;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemindCommand extends Command
{
    private $rotaRepository;
    private $notifier;
    private $blade;

    public function __construct(RotaRepositoryInterface $rotaRepository, Notifier $notifier, Blade $blade)
    {
        $this->rotaRepository = $rotaRepository;
        $this->notifier = $notifier;
        $this->blade = $blade;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('remind');
        $this->setDescription('Send reminders of upcoming rota.');
        $this->addArgument('date', InputArgument::OPTIONAL, 'The day to run the command for. Defaults to today.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $when = Carbon::createFromFormat('Y-m-d', $input->getArgument('date') ?? Carbon::now()->format('Y-m-d'));
        $when = $when->addDay();

        $rota = $this->rotaRepository->getRotaByName($when->copy()->startOfWeek()->format('Y-m-d'));

        if ($rota == null) {
            $output->writeln('<error>No rota to send reminders for.</error>');
            return;
        }

        $slot = $rota->getAssignedTimeSlots()->slotByName($when->format('l'));

        if ($slot == null) {
            $output->writeln('<error>No slot to send reminders for.</error>');
            return;
        }

        $slot->getAssignees()->each(function (Member $member) use ($slot, $when) {

            $notification = new Notification(
                [$member->getEmail()],
                'ROTA: It\'s your turn to do the rota tomorrow',
                $this->blade->view()->make('reminder-email', [
                    'slot' => $slot,
                    'you' => $member,
                    'when' => $when
                ])
            );

            $this->notifier->notify($notification);
        });

        $output->writeln('<info>Reminders sent.</info>');
    }
}
