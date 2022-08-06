<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Member;
use App\Repositories\CouponRepository;
use App\Repositories\MemberRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SignUpService
{
    protected CouponRepository $couponRepo;
    protected MemberRepository $memberRepo;

    public function __construct(MemberRepository $memberRepo, CouponRepository $couponRepo)
    {
        $this->memberRepo = $memberRepo;
        $this->couponRepo = $couponRepo;
    }

    public function createMember(array $data): Member
    {
        $where = [
            'phone' => $data['phone']
        ];

        $memberExists = $this->memberRepo
            ->query(compact('where'))
            ->exists();

        abort_if($memberExists, Response::HTTP_BAD_REQUEST, 'MEMBER_EXISTS');
        return $this->memberRepo->create($data);
    }

    public function redeemCoupon(Member $member): Coupon|null
    {
        for ($i = 0; $i < 10; $i++) {
            $coupon = DB::transaction(function () use ($member) {
                $coupon = $this->couponRepo
                    ->query()
                    ->whereNull('redeemed_at')
                    ->whereNull('member_id')
                    ->inRandomOrder()
                    ->lockForUpdate()
                    ->first();

                // coupons are all redeemed
                if (is_null($coupon)) {
                    Cache::rememberForever('coupon_unavailable', fn() => now()->toDateTimeString());
                    abort(Response::HTTP_BAD_REQUEST, 'COUPON_UNAVAILABLE');
                }

                // got a redeemed coupon
                if (!is_null($coupon->sent_at) || !is_null($coupon->user_id)) {
                    return null;
                }

                $coupon->update([
                    'member_id'   => $member->id,
                    'redeemed_at' => now()
                ]);

                return $coupon;
            });

            if (!is_null($coupon)) {
                return $coupon;
            }
        }

        // got redeemed coupons for 10 times
        return null;
    }

}