<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\SignUpService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    protected SignUpService $signUpService;

    public function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

    public function signUp(Request $request)
    {
        $lock = null;
        $data = $request->only(['phone', 'password']);

        try {
            $validator = validator($data, [
                'phone'    => 'bail|required|regex:/^[0-9][0-9]*$/',
                'password' => 'bail|required',
            ]);

            abort_if($validator->fails(), Response::HTTP_BAD_REQUEST, 'DATA_ERROR');

            $lock = Cache::store('lock')->lock('lock:signUp', 10);
            $lock->block(8);

            abort_if(Cache::has('coupon_unavailable'), Response::HTTP_BAD_REQUEST, 'COUPON_UNAVAILABLE');

            [$member, $coupon] = DB::transaction(function () use ($data) {
                $member = $this->signUpService->createMember($data);
                $coupon = $this->signUpService->redeemCoupon($member);
                return [$member, $coupon];
            });

            abort_if(is_null($coupon), Response::HTTP_BAD_REQUEST, 'COUPON_UNAVAILABLE');

            // save redemption record
            Redis::connection('coupon')->set(sprintf('%s:%s:%s', $member->id, $coupon->id, Str::random()), true);
            $lock->release();

            return response()->json([
                'result'  => true,
                'message' => 'Success',
                'data'    => [
                    'coupon_code' => $coupon->code
                ]
            ], Response::HTTP_OK);

        } catch (Throwable $t) {
            $status = Response::HTTP_BAD_REQUEST;
            $message = $t->getMessage();

            switch (true) {
                case $t instanceof HttpException:
                    optional($lock)->release();
                    break;
                case $t instanceof LockTimeoutException:
                    optional($lock)->release();
                    $status = Response::HTTP_LOCKED;
                    $message = 'RESOURCE_LOCKING';
                    break;
                default:
                    Log::error($t);
                    $status = Response::HTTP_INTERNAL_SERVER_ERROR;
                    $message = 'ERROR';
            }

            return response()->json([
                'result'  => false,
                'message' => $message,
                'data'    => []
            ], $status);
        }
    }
}