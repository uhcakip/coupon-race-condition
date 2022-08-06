<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $fillable = [
        'phone',
        'password',
    ];

    public function coupon(): HasOne
    {
        return $this->hasOne(Coupon::class);
    }
}