<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'member_id'   => null,
            'redeemed_at' => null,
        ];
    }
}
