<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'host_id',
        'card_config',
        'state',
        'emojis_blocked',
    ];

    protected $casts = [
        'card_config' => 'array',
        'emojis_blocked' => 'boolean',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_users')
            ->withPivot('role', 'is_online')
            ->withTimestamps();
    }

    public function voteSessions(): HasMany
    {
        return $this->hasMany(VoteSession::class);
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(VoteSession::class)->where('status', 'open')->latest();
    }

    public function emojis(): HasMany
    {
        return $this->hasMany(Emoji::class);
    }
}
