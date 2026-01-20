<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'is_used',
        'used_by_user_id',
        'used_at',
        'duration_days',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function isAvailable(): bool
    {
        return !$this->is_used;
    }

    public function activate(User $user): void
    {
        $this->update([
            'is_used' => true,
            'used_by_user_id' => $user->id,
            'used_at' => now(),
        ]);

        $user->update([
            'is_premium' => true,
            'premium_expires_at' => null, // Lifetime premium
            'max_activities' => 20,
        ]);
    }
}

