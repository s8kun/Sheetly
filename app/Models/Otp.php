<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Otp extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now());
    }
}
