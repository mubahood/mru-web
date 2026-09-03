<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Programme extends Model
{
    use GeneratesSlug;

    public const LEVELS = [
        'certificate' => 'Certificate',
        'diploma' => 'Diploma',
        'bachelor' => 'Bachelor\'s Degree',
        'postgraduate_diploma' => 'Postgraduate Diploma',
        'masters' => 'Master\'s Degree',
        'phd' => 'PhD',
    ];

    protected $fillable = [
        'faculty_id', 'name', 'slug', 'award_code', 'level', 'duration', 'study_modes',
        'tuition_per_semester', 'tuition_currency', 'tuition_note', 'entry_requirements',
        'description', 'career_prospects', 'intake_months', 'image', 'sort_order', 'is_published',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'study_modes' => 'array',
            'intake_months' => 'array',
            'is_published' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    /** @return BelongsTo<Faculty, $this> */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /** The homepage's curated best-foot-forward six, not a random sample. */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level] ?? ucfirst((string) $this->level);
    }

    public function tuitionDisplay(): ?string
    {
        if (! $this->tuition_per_semester) {
            return null;
        }

        return sprintf('%s %s per semester', $this->tuition_currency, number_format($this->tuition_per_semester));
    }
}
