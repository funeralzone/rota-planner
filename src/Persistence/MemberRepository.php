<?php

namespace ChrisHarrison\RotaPlanner\Persistence;

use ChrisHarrison\JsonRepository\Entities\Entity;
use ChrisHarrison\JsonRepository\Repositories\RepositoryInterface;
use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;
use ChrisHarrison\RotaPlanner\Model\TimeSlot;
use ChrisHarrison\RotaPlanner\Model\TimeSlotCollection;
use ChrisHarrison\RotaPlanner\Model\Repositories\MemberRepositoryInterface;

class MemberRepository implements MemberRepositoryInterface
{
    private $jsonRepository;

    public function __construct(RepositoryInterface $jsonRepository)
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

    public function getMemberById(string $id) : ?Member
    {
        $entity = $this->jsonRepository->getEntityById($id);

        if ($entity == null) {
            return null;
        }

        return $this->entityToMember($entity);
    }

    public function getMemberByEmail(string $email) : ?Member
    {
        $entities = $this->jsonRepository->getEntitiesByProperties(['email' => $email]);

        if ($entities->count() == 0) {
            return null;
        }

        return $this->entityToMember($entities->first());
    }

    public function putMember(Member $member) : void
    {
        $this->jsonRepository->putEntity($this->memberToEntity($member));
        return;
    }

    public function putMemberCollection(MemberCollection $collection) : void
    {
        $collection->each(function (Member $member) {
            $this->putMember($member);
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
            $entity->getProperty('timetasticId'),
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
            'timetasticId' => $member->getTimetasticId(),
            'email' => $member->getEmail(),
            'restrictedDays' => $restrictedDays,
            'contributionScore' => $member->getContributionScore()
        ]);
    }
}
