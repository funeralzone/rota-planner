<?php

namespace ChrisHarrison\RotaPlanner\Persistence;

use ChrisHarrison\JsonRepository\Entities\Entity;
use ChrisHarrison\JsonRepository\Persistence\JsonRepository;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;

class MemberRepository implements MemberRepositoryInterface
{
    private $jsonRepository;

    public function __construct(JsonRepository $jsonRepository)
    {
        $this->jsonRepository = $jsonRepository;
    }

    public function getAllMembers() : MemberCollection
    {
        $entities = $this->jsonRepository->getEntities();
        $members = new MemberCollection;
        $entities->each(function (Entity $entity) use (&$members) {
            $members = $members->add($this->entityToMember($entity));
        });
        return $members;
    }

    public function getMemberById(string $id) : Member
    {
        return $this->entityToMember($this->jsonRepository->getEntityById($id));
    }

    public function saveMembersCollection(MemberCollection $collection) : void
    {
        $collection->each(function (Member $member) {
            $this->jsonRepository->putEntity($this->memberToEntity($member));
        });

        return;
    }

    private function entityToMember(Entity $entity) : Member
    {
        $restrictedTimeSlots = new TimeSlotCollection;
        $restrictedDays = $entity->getProperty('restrictedDays');
        foreach ($restrictedDays as $day) {
            $restrictedTimeSlots = $restrictedTimeSlots->add(new TimeSlot($day));
        }

        return new Member(
            $entity->getId(),
            (string) $entity->getProperty('name'),
            (string) $entity->getProperty('email'),
            $restrictedTimeSlots,
            (int) $entity->getProperty('contributionScore')
        );
    }

    private function memberToEntity(Member $member) : Entity
    {
        $restrictedDays = [];
        foreach ($member->getRestrictedTimeSlots() as $timeSlot) {
            /* @var TimeSlot $timeSlot */
            $restrictedDays[] = $timeSlot->getName();
        }

        return new Entity($member->getId(), [
            'name' => $member->getName(),
            'email' => $member->getEmail(),
            'restrictedDays' => $restrictedDays,
            'contributionScore' => $member->getContributionScore()
        ]);
    }
}
