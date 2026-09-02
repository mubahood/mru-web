<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills a blank `slug` from the model's name-ish column on save, unique per
 * table. Lives in a trait because ten university content models need exactly
 * this and nothing else; a model with a different source column sets
 * `protected string $slugFrom = 'title';`.
 */
trait GeneratesSlug
{
    public static function bootGeneratesSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $source = property_exists($model, 'slugFrom') ? $model->slugFrom : 'name';
                $model->slug = static::uniqueSlugFor((string) $model->{$source}, $model->id);
            }
        });
    }

    public static function uniqueSlugFor(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($value, 180, '')) ?: Str::lower(Str::random(8));
        $slug = $base;
        $n = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }
}
