<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use GeneratesSlug;

    protected string $slugFrom = 'title';

    public const TYPES = [
        'journal' => 'Journal Article',
        'conference' => 'Conference Paper',
        'book' => 'Book',
        'book_chapter' => 'Book Chapter',
        'thesis' => 'Thesis / Dissertation',
        'report' => 'Technical Report',
    ];

    public const STATUSES = ['draft', 'pending', 'published'];

    protected $fillable = [
        'title', 'slug', 'abstract', 'type', 'journal_name', 'publisher', 'volume', 'issue',
        'pages', 'publication_date', 'year', 'doi', 'url', 'pdf_path', 'keywords',
        'citations', 'views', 'downloads', 'status', 'is_featured',
        'submitted_by', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'approved_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // The filter facets run on `year`; deriving it here keeps every write
        // path (form, importer, future self-submission) consistent.
        static::saving(function (Publication $publication) {
            if (blank($publication->year) && $publication->publication_date) {
                $publication->year = (int) $publication->publication_date->format('Y');
            }
        });
    }

    public function scholars(): BelongsToMany
    {
        return $this->belongsToMany(Scholar::class, 'publication_author')
            ->withPivot(['author_order'])
            ->orderBy('publication_author.author_order');
    }

    public function authorRows(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class)->orderBy('author_order');
    }

    public function researchAreas(): BelongsToMany
    {
        return $this->belongsToMany(ResearchArea::class, 'publication_research_area');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    /** All author names in order, MRU scholars and external co-authors alike. */
    public function authorNames(): array
    {
        return $this->authorRows
            ->map(fn ($row) => $row->scholar?->name ?? $row->external_name)
            ->filter()
            ->values()
            ->all();
    }
}
