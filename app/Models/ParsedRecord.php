<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ParsedRecord extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'aliases' => 'array',
            'weaknesses' => 'array',
            'references' => 'array',
            'raw_ranges' => 'array',
            'published_at' => 'datetime',
            'last_modified_at' => 'datetime',
            'resolved_at' => 'datetime',
            'known_exploited' => 'boolean',
            'cvss_score' => 'decimal:1',
        ];
    }

    public function ingestRecord(): BelongsTo
    {
        return $this->belongsTo(IngestRecord::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}