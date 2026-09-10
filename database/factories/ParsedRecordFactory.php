<?php

namespace Database\Factories;

use App\Models\IngestRecord;
use App\Models\ParsedRecord;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParsedRecord>
 */
class ParsedRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ingest_record_id' => IngestRecord::factory(),
            'source_id' => Source::factory(),
            'external_id' => 'CVE-'.fake()->year().'-'.fake()->numberBetween(1000, 99999),
            'raw_ranges' => [],
            'resolved_at' => null,
        ];
    }

    public function ofSource(Source $source): static
    {
        return $this->state(fn (): array => [
            'source_id' => $source->id,
            'ingest_record_id' => IngestRecord::factory()->state(['source_id' => $source->id]),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => ['resolved_at' => now()]);
    }
}
