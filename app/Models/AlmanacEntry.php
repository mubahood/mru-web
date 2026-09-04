<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmanacEntry extends Model
{
    /** Category → [label, Font Awesome icon]. The key is stored; this is the display. */
    public const CATEGORIES = [
        'academic' => ['Teaching & academic', 'fa-chalkboard-user'],
        'examination' => ['Examinations', 'fa-file-pen'],
        'governance' => ['Governance & committees', 'fa-scale-balanced'],
        'graduation' => ['Graduation', 'fa-graduation-cap'],
        'research' => ['Research & postgraduate', 'fa-flask'],
        'student' => ['Student life', 'fa-people-group'],
        'outreach' => ['Outreach & marketing', 'fa-bullhorn'],
        'holiday' => ['Public holiday & recess', 'fa-umbrella-beach'],
    ];

    protected $fillable = [
        'academic_year', 'semester', 'period', 'starts_on', 'ends_on',
        'activity', 'responsible', 'category', 'is_key_date', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_key_date' => 'boolean',
        ];
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category][0] ?? 'Academic year';
    }

    public function categoryIcon(): string
    {
        return self::CATEGORIES[$this->category][1] ?? 'fa-calendar-day';
    }

    /**
     * The month an entry belongs to, for grouping. Dated entries group by
     * their start date; undated ones (the "ongoing throughout the year"
     * activities) group under a single label so they never masquerade as
     * belonging to a particular month.
     */
    public function monthKey(): string
    {
        return $this->starts_on?->format('Y-m') ?? 'ongoing';
    }

    public function scopeDated($query)
    {
        return $query->whereNotNull('starts_on');
    }

    /** Entries running on, or starting after, a given day — the "what's next" feed. */
    public function scopeUpcoming($query, $from = null)
    {
        $from = $from ?: today();

        return $query->whereNotNull('starts_on')
            ->where(function ($q) use ($from) {
                $q->whereDate('starts_on', '>=', $from)
                    ->orWhereDate('ends_on', '>=', $from);
            });
    }
}
