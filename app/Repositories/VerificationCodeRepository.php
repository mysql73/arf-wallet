<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use App\Models\VerificationCode;
use App\Services\VerificationCodeService;

class VerificationCodeRepository
{
    public static function getByUserWhereNotExpire(User|string|int $user)
    {
        $user = $user instanceof User ? $user->id  : $user;

        return  VerificationCode::where("user_id", $user)
            ->whereDate(
                "expire_at",
                ">=",
                Carbon::now()->toDateString()
            )->whereTime("expire_at", ">", Carbon::now()->toTimeString())
            ->first();
    }

    public static function getByUserOrCreateIfExpireThenGet(User $user)
    { // get by user , if expire -> create then get the created data
        $verificationCode = static::getByUserWhereNotExpire($user)->code
            ?? static::createOrUpdateByUser($user);
        return $verificationCode;
    }

    public static function createOrUpdateByUser(User $user)
    {
        $code = rand(100000, 999999);
        VerificationCode::updateOrInsert(
            ["user_id" => $user->id, "email" => $user->email],
            [
                "code" => $code,
                "expire_at" => Carbon::now()->addMinutes(
                    VerificationCode::EXPIRE_TIME
                )->toDateTimeString()
            ]
        );
        return $code;
    }
}
