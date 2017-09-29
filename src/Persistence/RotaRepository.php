<?php

namespace ChrisHarrison\RotaPlanner\Persistence;

use ChrisHarrison\JsonRepository\Entities\Entity;
use ChrisHarrison\JsonRepository\Persistence\JsonRepository;
use ChrisHarrison\RotaPlanner\Model\AssignedTimeSlot;
use ChrisHarrison\RotaPlanner\Model\AssignedTimeSlotCollection;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\Rota;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;

class RotaRepository implements RotaRepositoryInterface
{
    private $jsonRepository;
    private $memberRepository;

    public function __construct(JsonRepository $jsonRepository, MemberRepositoryInterface $memberRepository)
    {
        $this->jsonRepository = $jsonRepository;
        $this->memberRepository = $memberRepository;
    }

    public function getRotaById(string $id) : ?Rota
    {
        return $this->entityToRota($this->jsonRepository->getEntityById($id));
    }

    public function getRotaByName(string $name) : ?Rota
    {
        return $this->entityToRota($this->jsonRepository->getEntitiesByProperties(['name' => $name])->first());
    }

    public function putRota(Rota $rota) : void
    {
        $this->jsonRepository->putEntity($this->rotaToEntity($rota));
        return;
    }

    private function entityToRota(Entity $entity) : Rota
    {
        $assignedTimeSlots = new AssignedTimeSlotCollection;
        $assignedTimeSlotsArray = $entity->getProperty('assignedTimeSlots');
        foreach ($assignedTimeSlotsArray as $assignedTimeSlot) {
            $assignees = new MemberCollection;
            foreach ($assignedTimeSlots['assignees'] as $assignee) {
                $assignees = $assignees->add($this->memberRepository->getMemberById($assignee));
            }
            $assignedTimeSlots = $assignedTimeSlots->add(new AssignedTimeSlot(
                new TimeSlot($assignedTimeSlot['timeSlot']),
                $assignees
            ));
        }

        return new Rota(
            $entity->getId(),
            $entity->getProperty('name'),
            $assignedTimeSlots
        );
    }

    private function rotaToEntity(Rota $rota) : Entity
    {
        $assignedTimeSlots = [];
        $rota->getAssignedTimeSlots()->each(function(AssignedTimeSlot $assignedTimeSlot) use (&$assignedTimeSlots) {
            $assignees = [];
            $assignedTimeSlot->getAssignees()->each(function (Member $member) use(&$assignees) {
                $assignees[] = $member->getId();
            });
            $assignedTimeSlots[] = [
                'timeSlot' => $assignedTimeSlot->getTimeSlot()->getName(),
                'assignees' => $assignees
            ];
        });

        return new Entity($rota->getId(), [
            'name' => $rota->getName(),
            'assignedTimeSlots' => $assignedTimeSlots
        ]);
    }
}
