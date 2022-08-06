<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'redeemed_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}