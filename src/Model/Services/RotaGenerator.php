<?php

namespace ChrisHarrison\RotaPlanner\Model\Services;

use ChrisHarrison\RotaPlanner\Model\AssignedTimeSlot;
use ChrisHarrison\RotaPlanner\Model\AssignedTimeSlotCollection;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\Rota;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;

class RotaGenerator
{
    private $idGenerator;
    private $incrementingNumber;

    public function __construct(IdGeneratorInterface $idGenerator, IncrementingNumber $incrementingNumber)
    {
        $this->idGenerator = $idGenerator;
        $this->incrementingNumber = $incrementingNumber;
    }

    public function generate(
        string $name,
        TimeSlotCollection $timeSlotCollection,
        MemberCollection $members,
        int $numberOfMembersPerTimeSlot
    ) : GeneratedRotaArtifact
    {
        if ($members->count() == 0) {
            throw new \InvalidArgumentException('Cannot generate a rota with no members.');
        }

        $assignedTimeSlots = new AssignedTimeSlotCollection;

        $timeSlotCollection->each(function (TimeSlot $timeSlot) use (&$assignedTimeSlots, &$members, $numberOfMembersPerTimeSlot) {
            $memberAvailable = true;
            $assignees = new MemberCollection;
            for ($i = 0; $i < $numberOfMembersPerTimeSlot; $i++) {
                try {
                    $chosenMember = $members->getBestMemberForTimeSlot($timeSlot);
                } catch (\Exception $e) {
                    if ($e->getCode() === 5000) {
                        $memberAvailable = false;
                        break;
                    } else {
                        throw $e;
                    }
                }

                $assignees = $assignees->add($chosenMember);
                $members = $members->removeMember($chosenMember);
                $chosenMember = $chosenMember->withContributionScore($this->incrementingNumber->get());
                $members = $members->add($chosenMember);
            }

            if ($memberAvailable) {
                $assignedTimeSlots = $assignedTimeSlots->add(new AssignedTimeSlot($timeSlot, $assignees));
            }
        });

        $rota = new Rota($this->idGenerator->generate(), $name, $assignedTimeSlots);

        return new GeneratedRotaArtifact($rota, $members);
    }
}
