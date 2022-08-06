<?php

namespace App\Repositories;

use App\Models\Member;

class MemberRepository extends Repository
{
    public function __construct(Member $member)
    {
        parent::__construct($member);
    }
}