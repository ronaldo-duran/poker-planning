<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoteSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'story_title',
        'story_description',
        'status',
        'average',
        'revealed_at',
    ];

    protected $casts = [
        'revealed_at' => 'datetime',
        'average' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
