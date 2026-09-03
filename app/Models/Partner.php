<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['name', 'logo', 'url', 'sort_order', 'show_on_home'];

    protected function casts(): array
    {
        return [
            'show_on_home' => 'boolean',
        ];
    }

    /** Every partner exists in the system; only some are curated onto the homepage. */
    public function scopeShowOnHome($query)
    {
        return $query->where('show_on_home', true);
    }
}
