<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use GeneratesSlug;

    protected string $slugFrom = 'title';

    protected $fillable = [
        'title', 'slug', 'reference_no', 'department', 'type', 'location',
        'deadline_on', 'summary', 'requirements', 'attachment', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'deadline_on' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOpen($query)
    {
        return $query->published()->where(fn ($q) => $q
            ->whereNull('deadline_on')
            ->orWhere('deadline_on', '>=', now()->toDateString()));
    }

    public function isOpen(): bool
    {
        return $this->deadline_on === null || $this->deadline_on->gte(now()->startOfDay());
    }
}
