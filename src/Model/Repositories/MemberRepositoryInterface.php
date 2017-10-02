<?php

namespace ChrisHarrison\RotaPlanner\Model\Repositories;

use ChrisHarrison\RotaPlanner\Model\Member;
use ChrisHarrison\RotaPlanner\Model\MemberCollection;

interface MemberRepositoryInterface
{
    public function getAllMembers() : MemberCollection;
    public function getMemberById(string $id) : ?Member;
    public function getMemberByEmail(string $email) : ?Member;
    public function putMember(Member $member) : void;
    public function putMemberCollection(MemberCollection $collection): void;
}
