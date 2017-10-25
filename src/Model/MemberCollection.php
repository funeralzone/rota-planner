<?php

namespace ChrisHarrison\RotaPlanner\Model;

use Collections\Collection;

class MemberCollection extends Collection
{
    public function __construct()
    {
        parent::__construct(Member::class);
    }

    public function removeMember(Member $member) : self
    {
        return $this->filter(function (Member $thisMember) use ($member) {
            return $thisMember->getName() != $member->getName();
        });
    }

    public function getBestMemberForTimeSlot(TimeSlot $timeSlot) : Member
    {
        $filtered = $this->filterMembersWhoCannotFulfillTimeSlot($timeSlot);
        if ($filtered->count() == 0) {
            throw new \Exception('Not enough members that can fulfill the time slots requested.', 5000);
        }
        return $filtered->sortByContributionScore()->last();
    }

    public function sortByContributionScore() : self
    {
        return $this->sort(function (Member $a, Member $b) {
            if ($a->getContributionScore() == $b->getContributionScore()) {
                return 0;
            }
            return ($a->getContributionScore() > $b->getContributionScore()) ? -1 : 1;
        });
    }

    public function filterMembersWhoCannotFulfillTimeSlot(TimeSlot $timeSlot) : self
    {
        return $this->filter(function (Member $member) use ($timeSlot) {
            return $member->isAvailable($timeSlot);
        });
    }
}
