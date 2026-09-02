<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmanacEntry extends Model
{
    protected $fillable = [
        'academic_year', 'semester', 'period', 'starts_on', 'ends_on', 'activity', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }
}
