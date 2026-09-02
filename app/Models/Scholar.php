<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Scholar extends Model
{
    use GeneratesSlug;

    protected $fillable = [
        'user_id', 'staff_member_id', 'name', 'slug', 'title', 'faculty_id', 'department',
        'bio', 'research_interests', 'photo', 'cv_path', 'email',
        'google_scholar_url', 'orcid', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    /** @return BelongsTo<Faculty, $this> */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /** @return BelongsTo<StaffMember, $this> */
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    /** @return BelongsToMany<Publication, $this> */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_author')
            ->withPivot(['author_order'])
            ->orderByDesc('publications.year');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('name');
    }

    public function displayName(): string
    {
        return trim(($this->title ? $this->title.' ' : '').$this->name);
    }
}
