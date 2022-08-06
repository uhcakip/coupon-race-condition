<?php

namespace App\Repositories;

use App\Models\Coupon;

class CouponRepository extends Repository
{
    public function __construct(Coupon $coupon)
    {
        parent::__construct($coupon);
    }
}