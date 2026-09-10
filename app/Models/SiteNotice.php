<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A notice in the strip above the site header.
 *
 * Deliberately scheduled rather than toggled. An intake announcement that has
 * to be switched off by hand is an intake announcement that will still be up in
 * March, so a notice carries its own window and retires itself.
 */
class SiteNotice extends Model
{
    protected $fillable = [
        'message', 'label', 'link_url', 'link_label', 'template', 'icon',
        'starts_at', 'ends_at', 'deadline_at',
        'is_published', 'is_dismissible', 'sort_order',
    ];

    /** The looks an editor can choose between. Key => what it is for. */
    public const TEMPLATES = [
        'ticker' => 'Ticker — scrolls across, for several notices or a long one',
        'banner' => 'Banner — a single calm line, centred',
        'urgent' => 'Urgent — red, for closures and emergencies',
        'countdown' => 'Countdown — shows the time left to a deadline',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'deadline_at' => 'datetime',
            'is_published' => 'boolean',
            'is_dismissible' => 'boolean',
        ];
    }

    /**
     * The notices that should be on screen right now.
     *
     * Both bounds are optional and treated as open: a notice with no dates runs
     * until someone unpublishes it, one with only an end runs until then.
     *
     * @param  Builder<SiteNotice>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $now = now();

        $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    /** Everything the strip needs, without the model leaking into the view. */
    public function templateName(): string
    {
        return array_key_exists($this->template, self::TEMPLATES) ? $this->template : 'ticker';
    }

    public function hasLink(): bool
    {
        return filled($this->link_url) && filled($this->link_label);
    }

    /** An external link must open in a new tab and be marked as leaving the site. */
    public function linkIsExternal(): bool
    {
        $host = parse_url((string) $this->link_url, PHP_URL_HOST);

        return $host !== null && $host !== parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * Whole days left until the deadline, or null when there is no deadline or
     * it has already passed.
     *
     * Counted from the start of today rather than from this instant, so a
     * deadline tomorrow evening reads "1 day left" all day today instead of
     * flickering between 0 and 1 depending on the hour.
     */
    public function daysLeft(): ?int
    {
        if (! $this->deadline_at instanceof Carbon) {
            return null;
        }

        $days = today()->diffInDays($this->deadline_at->copy()->startOfDay(), false);

        return $days >= 0 ? (int) $days : null;
    }

    /** A stable key the browser can remember a dismissal against. */
    public function dismissKey(): string
    {
        return 'mru-notice-'.$this->id.'-'.$this->updated_at?->timestamp;
    }
}
