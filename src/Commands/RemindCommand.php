<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use Carbon\Carbon;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\Repositories\RotaRepositoryInterface;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPMailer\PHPMailer\PHPMailer as Mailer;

class RemindCommand extends Command
{
    private $rotaRepository;
    private $mailer;
    private $blade;

    public function __construct(RotaRepositoryInterface $rotaRepository, Mailer $mailer, Blade $blade)
    {
        $this->rotaRepository = $rotaRepository;
        $this->mailer = $mailer;
        $this->blade = $blade;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('remind');
        $this->setDescription('Send reminders of upcoming rota.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $now = Carbon::now();
        $rota = $this->rotaRepository->getRotaByName($now->startOfWeek()->format('Y-m-d'));

        if ($rota == null) {
            return;
        }

        $slot = $rota->getAssignedTimeSlots()->slotByName($now->format('l'));

        if ($slot == null) {
            return;
        }

        $slot->getAssignees()->each(function (Member $member) use ($slot, $now) {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($member->getEmail(), $member->getName());
            $this->mailer->Subject = 'ROTA: It\'s your turn to do the rota today';
            $this->mailer->Body = $this->blade->view()->make('reminder-email', [
                'slot' => $slot,
                'you' => $member,
                'now' => $now
            ]);
            $this->mailer->send();
        });
    }
}
