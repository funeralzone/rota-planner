<?php

namespace ChrisHarrison\RotaPlanner\Commands;

use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\Repositories\MemberRepositoryInterface;
use ChrisHarrison\RotaPlanner\Model\Services\IdGeneratorInterface;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;
use ChrisHarrison\TimetasticAPI\Client as TimetasticClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncMembersCommand extends Command
{
    private $timetasticClient;
    private $membersRepository;
    private $idGenerator;

    public function __construct(
        TimetasticClient $timetasticClient,
        MemberRepositoryInterface $memberRepository,
        IdGeneratorInterface $idGenerator
    )
    {
        $this->timetasticClient = $timetasticClient;
        $this->membersRepository = $memberRepository;
        $this->idGenerator = $idGenerator;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sync:members');
        $this->setDescription('Sync members with external service.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $usersResponse = $this->timetasticClient->getUsers();
        $users = json_decode($usersResponse->getBody()->getContents(), true);

        foreach ($users as $user) {
            $existingMember = $this->membersRepository->getMemberByEmail($user['email']);

            if ($existingMember !== null) {
                $id = $existingMember->getId();
                $restrictedTimeSlots = $existingMember->getRestrictedTimeSlots();
                $contributionScore = $existingMember->getContributionScore();
            } else {
                $id = $this->idGenerator->generate();
                $restrictedTimeSlots = new TimeSlotCollection;
                $contributionScore = 0;
            }

            $timetasticId = $user['id'];
            $name =  $user['firstname'] . ' ' . strtoupper(substr($user['surname'], 0, 1));
            $email = $user['email'];

            $member = new Member(
                $id,
                $timetasticId,
                $name,
                $email,
                $restrictedTimeSlots,
                $contributionScore
            );
            $this->membersRepository->putMember($member);
        }
    }
}
