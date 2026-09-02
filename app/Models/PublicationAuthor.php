<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationAuthor extends Model
{
    public $timestamps = false;

    protected $table = 'publication_author';

    protected $fillable = ['publication_id', 'scholar_id', 'external_name', 'author_order'];

    /** @return BelongsTo<Scholar, $this> */
    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    /** @return BelongsTo<Publication, $this> */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
