<?php

namespace ChrisHarrison\RotaPlanner\Persistence;

use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;

interface MemberRepositoryInterface
{
    public function getAllMembers() : MemberCollection;
    public function getMemberById(string $id) : Member;
    public function saveMembersCollection(MemberCollection $collection): void;
}
