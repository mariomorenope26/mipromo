<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $fillable = [
        'institution_id',
        'promotion_id',
        'classroom_id',
        'type',
        'name',
        'slug',
        'description',
        'is_private',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommunityEvent::class)->orderBy('starts_at');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(CommunityPoll::class)->latest();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(CommunityCollection::class)->latest();
    }

    public function albums(): HasMany
    {
        return $this->hasMany(CommunityAlbum::class)->latest();
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'institution' => 'Comunidad Institucional',
            'promotion' => 'Comunidad de Promoción',
            'classroom' => 'Comunidad de Aula',
            default => 'Grupo de Interés',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
