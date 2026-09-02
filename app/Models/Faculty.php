<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use GeneratesSlug;

    protected $table = 'faculties';

    protected $fillable = [
        'name', 'slug', 'short_name', 'tagline', 'description', 'about', 'vision', 'mission',
        'color', 'icon', 'departments', 'careers', 'dean_staff_id', 'cover_image',
        'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'departments' => 'array',
            'careers' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class)->orderBy('sort_order')->orderBy('name');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StaffMember::class)->orderBy('sort_order')->orderBy('name');
    }

    public function events(): HasMany
    {
        return $this->hasMany(UniversityEvent::class);
    }

    public function dean(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'dean_staff_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }
}
