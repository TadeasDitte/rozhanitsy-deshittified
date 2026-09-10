<?php

namespace Database\Factories;

use App\Models\IngestRecord;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IngestRecord>
 */
class IngestRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'external_id' => 'CVE-'.fake()->year().'-'.fake()->numberBetween(1000, 99999),
            'raw_payload' => [],
            'fetched_at' => now(),
            'processed_at' => now(),
            'processing_status' => 'processed',
            'processing_error' => null,
        ];
    }
}
