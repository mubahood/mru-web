<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use GeneratesSlug;

    protected $fillable = [
        'name', 'slug', 'category', 'coverage', 'criteria', 'amount_note',
        'description', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }
}
