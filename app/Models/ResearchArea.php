<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResearchArea extends Model
{
    use GeneratesSlug;

    protected $fillable = ['name', 'slug', 'description'];

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_research_area');
    }
}
